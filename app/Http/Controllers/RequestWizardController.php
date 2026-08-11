<?php

namespace App\Http\Controllers;

use App\Concerns\PasswordValidationRules;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\RequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RequestWizardController extends Controller
{
    use PasswordValidationRules;

    public function show(Request $request): Response|RedirectResponse
    {
        // Externally-fulfilled services are never bookable here. Landing on
        // the wizard with one preselected sends the customer to that
        // service's own page, which carries the partner hand-off instead.
        if ($preselected = $request->query('service')) {
            $type = ServiceType::where('slug', $preselected)->first();

            if ($type?->isExternal()) {
                return redirect()->route('service-type', $type->slug);
            }
        }

        $activeTypes = ServiceType::where('is_active', true)
            ->where('flow_mode', '!=', 'external')
            ->orderBy('sort_order')
            ->with('fields')
            ->get();

        return Inertia::render('wizard/RequestWizard', [
            'serviceTypes' => $activeTypes->map(fn ($t) => [
                'id' => $t->id, 'name' => $t->name, 'slug' => $t->slug, 'description' => $t->description,
                'flowMode' => $t->flow_mode,
            ])->values(),
            // Keyed by service_type_id so the frontend can pick the right set
            // once a service is chosen in step 1, without a second round-trip.
            'serviceTypeFields' => $activeTypes->mapWithKeys(fn ($t) => [
                $t->id => $t->fields->map(fn ($f) => [
                    'key' => $f->key, 'label' => $f->label, 'type' => $f->type,
                    'options' => $f->options, 'isRequired' => $f->is_required,
                ]),
            ]),
            'preselected' => $request->query('service'),
        ]);
    }

    public function store(Request $request, RequestService $requestService): RedirectResponse
    {
        $isGuest = ! Auth::check();

        $request->validate(['service_type_id' => ['required', 'exists:service_types,id']]);
        $serviceType = ServiceType::with('fields')->findOrFail($request->input('service_type_id'));

        // Backstop for the external services: the UI never offers this path,
        // so reaching here means a hand-crafted POST. Refuse it outright
        // rather than creating a request nobody on the platform can fulfil.
        abort_if($serviceType->isExternal(), 404);

        $hasCustomFields = $serviceType->fields->isNotEmpty();

        $rules = [
            'service_type_id' => ['required', 'exists:service_types,id'],
            'vehicle_make' => [$hasCustomFields ? 'nullable' : 'required', 'string', 'max:80'],
            'vehicle_model' => [$hasCustomFields ? 'nullable' : 'required', 'string', 'max:120'],
            'first_registration' => ['nullable', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'mileage' => ['nullable', 'integer', 'min:0', 'max:2000000'],
            'vin' => ['nullable', 'string', 'size:17', 'regex:/^[A-HJ-NPR-Z0-9]{17}$/i'],
            'fuel_type' => ['nullable', 'string', 'max:30'],
            'transmission' => ['nullable', 'string', 'max:30'],
            'plz' => ['required', 'digits:5'],
            'ort' => ['required', 'string', 'max:120'],
            'strasse' => ['nullable', 'string', 'max:190'],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'alternative_date' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'answers' => ['nullable', 'array'],
            // Only asked for direct-accept services, and mandatory there.
            'accident_role' => [$serviceType->isDirectAccept() ? 'required' : 'nullable', Rule::in(['geschaedigter', 'verursacher', 'unklar'])],
            'has_lawyer' => [$serviceType->isDirectAccept() ? 'required' : 'nullable', 'boolean'],
            'contact_name' => ['required', 'string', 'max:120'],
            'contact_email' => ['required', 'email', 'max:190'],
            'contact_phone' => ['required', 'string', 'max:40', 'regex:/^[+0-9][0-9 \/\-()]{5,}$/'],
            'agb' => ['accepted'],
            'privacy' => ['accepted'],
            'photos' => ['nullable', 'array', 'max:8'],
            'photos.*' => ['image', 'max:8192'],
        ];

        $messages = [
            'vin.regex' => 'Die FIN/VIN muss aus 17 Zeichen bestehen (ohne I, O und Q).',
            'first_registration.regex' => 'Bitte geben Sie die Erstzulassung im Format MM/JJJJ an, z. B. 03/2019.',
            'plz.digits' => 'Bitte geben Sie eine gültige fünfstellige Postleitzahl ein.',
            'accident_role.required' => 'Bitte geben Sie Ihre Rolle bei dem Unfall an.',
            'has_lawyer.required' => 'Bitte geben Sie an, ob Sie bereits einen Anwalt beauftragt haben.',
            'agb.accepted' => 'Bitte akzeptieren Sie die AGB.',
            'privacy.accepted' => 'Bitte akzeptieren Sie die Datenschutzerklärung.',
        ];

        if ($hasCustomFields) {
            foreach ($serviceType->fields as $field) {
                $key = "answers.{$field->key}";
                $rules[$key] = [
                    $field->is_required ? 'required' : 'nullable',
                    ...match ($field->type) {
                        'text' => ['string', 'max:1000'],
                        'textarea' => ['string', 'max:5000'],
                        'number' => ['numeric'],
                        'date' => ['date'],
                        'select' => [Rule::in($field->options ?? [])],
                        'file' => ['file', 'image', 'max:8192'],
                    },
                ];
            }
        }

        $data = $request->validate($rules, $messages);

        if ($hasCustomFields) {
            foreach ($serviceType->fields->where('type', 'file') as $field) {
                if ($request->hasFile("answers.{$field->key}")) {
                    $data['answers'][$field->key] = $request->file("answers.{$field->key}")
                        ->store('request-photos', 'public');
                }
            }
        }

        $user = $isGuest ? null : Auth::user();

        $photoPaths = [];
        foreach ($request->file('photos', []) as $photo) {
            $photoPaths[] = [
                'path' => $photo->store('request-photos', 'public'),
                'original_name' => $photo->getClientOriginalName(),
            ];
        }

        $serviceRequest = $requestService->submit(
            $user,
            collect($data)->except(['agb', 'privacy', 'photos'])->all(),
            $photoPaths
        );

        if ($isGuest) {
            // No account exists yet for this submission. Grant this browser
            // session access to the confirmation page (and the optional
            // post-submission password screen) without logging anyone in.
            $request->session()->put("request_access_{$serviceRequest->id}", true);
        }

        return redirect()->route('wizard.confirmation', $serviceRequest->request_number);
    }

    public function confirmation(Request $request, ServiceRequest $serviceRequest): Response
    {
        $isOwner = Auth::check() && Auth::id() === $serviceRequest->user_id;
        $hasGuestAccess = $request->session()->get("request_access_{$serviceRequest->id}") === true;
        abort_unless($isOwner || $hasGuestAccess, 403);

        $isUnclaimedGuest = $serviceRequest->user_id === null && ! Auth::check();
        $accountAlreadyExists = $isUnclaimedGuest && User::where('email', $serviceRequest->contact_email)->exists();

        return Inertia::render('wizard/Confirmation', [
            'request' => [
                'number' => $serviceRequest->request_number,
                'matched' => $serviceRequest->matched_count,
                'unmatched' => $serviceRequest->status === 'unmatched',
                'service' => $serviceRequest->serviceType->name,
                'ort' => $serviceRequest->ort,
                'directAccept' => $serviceRequest->serviceType->isDirectAccept(),
            ],
            // Never prompt to set a password for an email that already has an
            // account — that would only invite confusion. Offer to log in instead.
            'canClaim' => $isUnclaimedGuest && ! $accountAlreadyExists,
            'canLogin' => $accountAlreadyExists,
            'contactEmail' => $isUnclaimedGuest ? $serviceRequest->contact_email : null,
            'contactName' => $isUnclaimedGuest ? $serviceRequest->contact_name : null,
        ]);
    }

    public function claimAccount(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $hasGuestAccess = $request->session()->get("request_access_{$serviceRequest->id}") === true;
        abort_unless($hasGuestAccess && ! Auth::check(), 403);

        if ($serviceRequest->user_id !== null) {
            return back()->with('error', 'Für diese Anfrage besteht bereits ein Konto.');
        }

        if (User::where('email', $serviceRequest->contact_email)->exists()) {
            return back()->with('error', 'Für diese E-Mail-Adresse existiert bereits ein Konto. Bitte melden Sie sich an.');
        }

        $data = $request->validate(['password' => $this->passwordRules()]);

        $user = User::create([
            'name' => $serviceRequest->contact_name,
            'email' => $serviceRequest->contact_email,
            'phone' => $serviceRequest->contact_phone,
            'password' => $data['password'],
            'agb_accepted' => true,
            'privacy_accepted_at' => now(),
        ]);

        Auth::login($user);

        return redirect()->route('wizard.confirmation', $serviceRequest->request_number)
            ->with('success', 'Konto erstellt! Sie können Ihre Anfrage jetzt jederzeit verfolgen.');
    }
}
