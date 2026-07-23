<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\InspectorAuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Customer\CustomerAreaController;
use App\Http\Controllers\Inspector\InspectorAreaController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RequestWizardController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Public / Marketing
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/kfz-gutachten', [PublicController::class, 'category'])->name('category');
Route::get('/kfz-gutachten/{serviceType:slug}', [PublicController::class, 'serviceType'])->name('service-type');
Route::get('/so-funktionierts', [PublicController::class, 'howItWorks'])->name('how-it-works');
Route::get('/fuer-gutachter', [PublicController::class, 'forInspectors'])->name('for-inspectors');
Route::get('/ueber-uns', [PublicController::class, 'about'])->name('about');
Route::get('/kontakt', [PublicController::class, 'contact'])->name('contact');
Route::post('/kontakt', [PublicController::class, 'submitContact'])->middleware('throttle:5,10')->name('contact.submit');
Route::get('/preise', [PublicController::class, 'pricing'])->name('pricing');
Route::get('/faq', [PublicController::class, 'faq'])->name('faq');
Route::get('/bewertungen', [PublicController::class, 'reviews'])->name('reviews');
Route::get('/impressum', [PublicController::class, 'imprint'])->name('imprint');
Route::get('/datenschutz', [PublicController::class, 'privacy'])->name('privacy');
Route::get('/agb', [PublicController::class, 'terms'])->name('terms');
Route::get('/cookie-richtlinie', [PublicController::class, 'cookies'])->name('cookies');
Route::get('/demnaechst/{category:slug}', [PublicController::class, 'comingSoon'])->name('coming-soon');
Route::post('/demnaechst/{category:slug}/interesse', [PublicController::class, 'registerInterest'])
    ->middleware('throttle:10,1')->name('coming-soon.interest');

/*
|--------------------------------------------------------------------------
| Request wizard (guests allowed)
|--------------------------------------------------------------------------
*/
Route::get('/anfrage', [RequestWizardController::class, 'show'])->name('wizard');
Route::post('/anfrage', [RequestWizardController::class, 'store'])->middleware('throttle:10,10')->name('wizard.store');
Route::get('/anfrage/bestaetigung/{serviceRequest:request_number}', [RequestWizardController::class, 'confirmation'])->name('wizard.confirmation');

/*
|--------------------------------------------------------------------------
| Customer area (web guard)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('konto')->name('konto.')->group(function () {
    Route::get('/', [CustomerAreaController::class, 'dashboard'])->name('dashboard');
    Route::get('/anfragen', [CustomerAreaController::class, 'requests'])->name('requests');
    Route::get('/anfragen/{serviceRequest}', [CustomerAreaController::class, 'requestDetail'])->name('requests.show');
    Route::get('/anfragen/{serviceRequest}/angebote', [CustomerAreaController::class, 'compareOffers'])->name('requests.offers');
    Route::get('/auftraege', [CustomerAreaController::class, 'bookings'])->name('bookings');
    Route::get('/auftraege/{booking}', [CustomerAreaController::class, 'bookingDetail'])->name('bookings.show');
    Route::post('/auftraege/{booking}/bewertung', [CustomerAreaController::class, 'storeReview'])->name('bookings.review');
    Route::get('/zahlungen', [CustomerAreaController::class, 'payments'])->name('payments');

    Route::post('/angebote/{offer}/annehmen', [CheckoutController::class, 'accept'])->name('offers.accept');
});

Route::middleware('auth')->group(function () {
    Route::get('/kasse/erfolg', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/kasse/abgebrochen', [CheckoutController::class, 'cancelled'])->name('checkout.cancelled');
});

Route::post('/stripe/webhook', [CheckoutController::class, 'webhook'])->name('stripe.webhook');

// Starter-kit dashboard route kept as a redirect
Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/konto')->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Inspector area (inspector guard)
|--------------------------------------------------------------------------
*/
Route::prefix('gutachter')->name('gutachter.')->group(function () {
    Route::get('/login', [InspectorAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [InspectorAuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::post('/logout', [InspectorAuthController::class, 'logout'])->name('logout');
});

Route::get('/registrieren/gutachter', [\App\Http\Controllers\Auth\InspectorRegisterController::class, 'show'])->name('gutachter.register');
Route::post('/registrieren/gutachter', [\App\Http\Controllers\Auth\InspectorRegisterController::class, 'store'])->middleware('throttle:10,10')->name('gutachter.register.store');

Route::get('/gutachter/anfragen/{request}/direkt/{inspector}', [InspectorAreaController::class, 'signedRequest'])
    ->middleware('signed')->name('inspector.requests.signed');

Route::middleware('auth:inspector')->prefix('gutachter')->name('inspector.')->group(function () {
    Route::get('/', [InspectorAreaController::class, 'dashboard'])->name('dashboard');
    Route::get('/anfragen', [InspectorAreaController::class, 'requests'])->name('requests');
    Route::get('/anfragen/{serviceRequest}', [InspectorAreaController::class, 'requestDetail'])->name('requests.show');
    Route::get('/anfragen/{serviceRequest}/angebot', [InspectorAreaController::class, 'offerForm'])->name('requests.offer');
    Route::post('/anfragen/{serviceRequest}/angebot', [InspectorAreaController::class, 'storeOffer'])->name('requests.offer.store');
    Route::get('/angebote', [InspectorAreaController::class, 'offers'])->name('offers');
    Route::get('/auftraege', [InspectorAreaController::class, 'jobs'])->name('jobs');
    Route::get('/auftraege/{booking}', [InspectorAreaController::class, 'jobDetail'])->name('jobs.show');
    Route::post('/auftraege/{booking}/abschliessen', [InspectorAreaController::class, 'completeJob'])->name('jobs.complete');
    Route::get('/servicegebiet', [InspectorAreaController::class, 'serviceAreas'])->name('service-areas');
    Route::post('/servicegebiet', [InspectorAreaController::class, 'storeServiceArea'])->name('service-areas.store');
    Route::delete('/servicegebiet/{area}', [InspectorAreaController::class, 'deleteServiceArea'])->name('service-areas.delete');
    Route::get('/wallet', [InspectorAreaController::class, 'wallet'])->name('wallet');
    Route::get('/wallet/auszahlung', [InspectorAreaController::class, 'payoutForm'])->name('payout');
    Route::post('/wallet/auszahlung', [InspectorAreaController::class, 'storePayout'])->name('payout.store');
    Route::get('/profil', [InspectorAreaController::class, 'profile'])->name('profile');
    Route::post('/profil', [InspectorAreaController::class, 'updateProfile'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| Admin area (admin guard)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:10,1')->name('admin.login.store');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

Route::middleware('auth:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/anfragen', [AdminController::class, 'requests'])->name('requests');
    Route::get('/anfragen/{serviceRequest}', [AdminController::class, 'requestDetail'])->name('requests.show');
    Route::get('/angebote', [AdminController::class, 'offers'])->name('offers');
    Route::get('/auftraege', [AdminController::class, 'bookings'])->name('bookings');
    Route::get('/auftraege/{booking}', [AdminController::class, 'bookingDetail'])->name('bookings.show');
    Route::post('/auftraege/{booking}/bestaetigen', [AdminController::class, 'confirmBooking'])->name('bookings.confirm');
    Route::get('/zahlungen', [AdminController::class, 'payments'])->name('payments');
    Route::get('/provisionen', [AdminController::class, 'commissions'])->name('commissions');
    Route::get('/gutachter', [AdminController::class, 'inspectors'])->name('inspectors');
    Route::get('/gutachter/import', [AdminController::class, 'importForm'])->name('inspectors.import');
    Route::post('/gutachter/import/vorschau', [AdminController::class, 'importPreview'])->name('inspectors.import.preview');
    Route::post('/gutachter/import', [AdminController::class, 'importStore'])->name('inspectors.import.store');
    Route::get('/gutachter/{inspector}', [AdminController::class, 'inspectorDetail'])->name('inspectors.show');
    Route::post('/gutachter/{inspector}/status', [AdminController::class, 'toggleInspector'])->name('inspectors.toggle');
    Route::get('/wallets', [AdminController::class, 'wallets'])->name('wallets');
    Route::get('/auszahlungen', [AdminController::class, 'payouts'])->name('payouts');
    Route::post('/auszahlungen/{payout}/bezahlt', [AdminController::class, 'markPayoutPaid'])->name('payouts.paid');
    Route::get('/kunden', [AdminController::class, 'customers'])->name('customers');
    Route::get('/dienstleistungen', [AdminController::class, 'services'])->name('services');
    Route::post('/kategorien/{category}/status', [AdminController::class, 'toggleCategory'])->name('categories.toggle');
    Route::get('/einstellungen', [AdminController::class, 'settings'])->name('settings');
    Route::post('/einstellungen', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/protokolle', [AdminController::class, 'logs'])->name('logs');
});

require __DIR__.'/settings.php';
