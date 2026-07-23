<?php

namespace App\Http\Controllers;

use App\Http\Middleware\HandleLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $locale = $request->string('locale')->toString();

        if (in_array($locale, HandleLocale::SUPPORTED, true)) {
            $request->session()->put('locale', $locale);
        }

        return back();
    }
}
