<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Inspector;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InspectorRegisterController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('auth/InspectorRegister');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['nullable', 'string', 'max:190'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:inspectors,email'],
            'phone' => ['required', 'string', 'max:40'],
            'city' => ['required', 'string', 'max:120'],
            'password' => ['required', 'confirmed', 'min:8'],
            'plz_from' => ['nullable', 'digits:5'],
            'plz_to' => ['nullable', 'digits:5', 'gte:plz_from'],
            'agb' => ['accepted'],
        ], [
            'agb.accepted' => 'Bitte akzeptieren Sie die AGB.',
            'email.unique' => 'Für diese E-Mail-Adresse existiert bereits ein Gutachter-Konto.',
        ]);

        $inspector = Inspector::create([
            'company_name' => $data['company_name'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'city' => $data['city'],
            'password' => $data['password'],
            'is_active' => true,
            'is_verified' => false,
            'member_since' => now(),
            'email_verified_at' => now(),
        ]);

        $inspector->serviceAreas()->create(['type' => 'city', 'city_name' => $data['city']]);
        if (! empty($data['plz_from']) && ! empty($data['plz_to'])) {
            $inspector->serviceAreas()->create([
                'type' => 'postal_range',
                'postal_from' => (int) $data['plz_from'],
                'postal_to' => (int) $data['plz_to'],
            ]);
        }

        Wallet::firstOrCreate(['inspector_id' => $inspector->id]);
        ActivityLog::record('inspector.registered', $inspector);

        Auth::guard('inspector')->login($inspector);

        return redirect('/gutachter')->with('success', 'Willkommen! Ihr Gutachter-Konto wurde erstellt.');
    }
}
