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

    public function show(Request $request): Response
    {
        return Inertia::render('wizard/RequestWizard', [
            'serviceTypes' => ServiceType::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug', 'description']),
            'preselected' => $request->query('service'),
        ]);
    }

    public function store(Request $request, RequestService $requestService): RedirectResponse
    {
        $isGuest = ! Auth::check();

        $rules = [
            'service_type_id' => ['required', 'exists:service_types,id'],
            'vehicle_make' => ['required', 'string', 'max:80'],
            'vehicle_model' => ['required', 'string', 'max:120'],
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
            'agb.accepted' => 'Bitte akzeptieren Sie die AGB.',
            'privacy.accepted' => 'Bitte akzeptieren Sie die Datenschutzerklärung.',
        ];

        if ($isGuest) {
            $rules['contact_email'][] = Rule::unique('users', 'email');
            $rules['password'] = $this->passwordRules();
            $messages['contact_email.unique'] = 'Für diese E-Mail-Adresse existiert bereits ein Konto. Bitte melden Sie sich an, um eine Anfrage zu stellen.';
        }

        $data = $request->validate($rules, $messages);

        if ($isGuest) {
            $user = User::create([
                'name' => $data['contact_name'],
                'email' => $data['contact_email'],
                'phone' => $data['contact_phone'],
                'password' => $data['password'],
                'agb_accepted' => true,
                'privacy_accepted_at' => now(),
            ]);
            Auth::login($user);
        } else {
            $user = Auth::user();
        }

        $photoPaths = [];
        foreach ($request->file('photos', []) as $photo) {
            $photoPaths[] = [
                'path' => $photo->store('request-photos', 'public'),
                'original_name' => $photo->getClientOriginalName(),
            ];
        }

        $serviceRequest = $requestService->submit(
            $user,
            collect($data)->except(['agb', 'privacy', 'photos', 'password', 'password_confirmation'])->all(),
            $photoPaths
        );

        return redirect()->route('wizard.confirmation', $serviceRequest->request_number);
    }

    public function confirmation(ServiceRequest $serviceRequest): Response
    {
        abort_unless(Auth::id() === $serviceRequest->user_id, 403);

        return Inertia::render('wizard/Confirmation', [
            'request' => [
                'number' => $serviceRequest->request_number,
                'matched' => $serviceRequest->matched_count,
                'unmatched' => $serviceRequest->status === 'unmatched',
                'service' => $serviceRequest->serviceType->name,
                'ort' => $serviceRequest->ort,
            ],
        ]);
    }
}
