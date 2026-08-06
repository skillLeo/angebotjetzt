<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\InspectorAuthController;
use App\Http\Controllers\Auth\InspectorRegisterController;
use App\Http\Controllers\Customer\CustomerAreaController;
use App\Http\Controllers\Inspector\GuestOfferController;
use App\Http\Controllers\Inspector\InspectorAreaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfferAcceptanceController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RequestWizardController;
use App\Http\Controllers\ReviewSurveyController;
use App\Http\Controllers\SeoController;
use App\Http\Middleware\EnsureInspectorOnboardingComplete;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

Route::get('/reviews/{booking}/survey', [ReviewSurveyController::class, 'show'])
    ->middleware('signed')->name('reviews.survey.show');
Route::post('/reviews/{booking}/survey', [ReviewSurveyController::class, 'store'])->name('reviews.survey.store');
Route::get('/reviews/survey/thanks', fn () => Inertia::render('public/ReviewThanks'))->name('reviews.survey.thanks');

/*
|--------------------------------------------------------------------------
| Public / Marketing
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/vehicle-reports', [PublicController::class, 'category'])->name('category');
Route::get('/vehicle-reports/{serviceType:slug}', [PublicController::class, 'serviceType'])->name('service-type');
Route::get('/how-it-works', [PublicController::class, 'howItWorks'])->name('how-it-works');
Route::get('/for-inspectors', [PublicController::class, 'forInspectors'])->name('for-inspectors');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicController::class, 'submitContact'])->middleware('throttle:5,10')->name('contact.submit');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
Route::get('/faq', [PublicController::class, 'faq'])->name('faq');
Route::get('/reviews', [PublicController::class, 'reviews'])->name('reviews');
Route::get('/imprint', [PublicController::class, 'imprint'])->name('imprint');
Route::get('/privacy', [PublicController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PublicController::class, 'terms'])->name('terms');
Route::get('/cookie-policy', [PublicController::class, 'cookies'])->name('cookies');
Route::get('/coming-soon/{category:slug}', [PublicController::class, 'comingSoon'])->name('coming-soon');
Route::get('/request/start', [PublicController::class, 'requestStart'])->name('request.start');
Route::post('/coming-soon/{category:slug}/interest', [PublicController::class, 'registerInterest'])
    ->middleware('throttle:10,1')->name('coming-soon.interest');

/*
|--------------------------------------------------------------------------
| Request wizard (guests may fill it out; an account is created at submission)
|--------------------------------------------------------------------------
*/
Route::get('/request', [RequestWizardController::class, 'show'])->name('wizard');
Route::post('/request', [RequestWizardController::class, 'store'])->middleware('throttle:10,10')->name('wizard.store');
// Guests may view their own confirmation page and optionally claim an account
// afterward without ever having logged in — authorization is handled inside
// the controller (session-scoped guest access or authenticated ownership).
Route::get('/request/confirmation/{serviceRequest:request_number}', [RequestWizardController::class, 'confirmation'])->name('wizard.confirmation');
Route::post('/request/confirmation/{serviceRequest:request_number}/claim', [RequestWizardController::class, 'claimAccount'])
    ->middleware('throttle:5,10')->name('wizard.confirmation.claim');

/*
|--------------------------------------------------------------------------
| Customer area (web guard)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('account')->name('konto.')->group(function () {
    Route::get('/', [CustomerAreaController::class, 'dashboard'])->name('dashboard');
    Route::get('/requests', [CustomerAreaController::class, 'requests'])->name('requests');
    Route::get('/requests/{serviceRequest}', [CustomerAreaController::class, 'requestDetail'])->name('requests.show');
    Route::get('/requests/{serviceRequest}/offers', [CustomerAreaController::class, 'compareOffers'])->name('requests.offers');
    Route::get('/bookings', [CustomerAreaController::class, 'bookings'])->name('bookings');
    Route::get('/bookings/{booking}', [CustomerAreaController::class, 'bookingDetail'])->name('bookings.show');
    Route::post('/bookings/{booking}/review', [CustomerAreaController::class, 'storeReview'])->name('bookings.review');
    Route::get('/payments', [CustomerAreaController::class, 'payments'])->name('payments');

    Route::post('/offers/{offer}/accept', [OfferAcceptanceController::class, 'accept'])->name('offers.accept');
});

Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
    ->middleware('auth:web,inspector')
    ->name('notifications.read-all');

// Starter-kit dashboard route kept as a redirect
Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/account')->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Inspector area (inspector guard)
|--------------------------------------------------------------------------
*/
Route::prefix('inspector')->name('gutachter.')->group(function () {
    // The standalone inspector login page is retired — /login now authenticates
    // both customers and inspectors. The route name stays alive as a redirect
    // so existing route('gutachter.login') references (e.g. invitation emails)
    // keep working and land on the correct unified page.
    Route::get('/login', fn () => redirect()->route('login'))->name('login');
    Route::post('/logout', [InspectorAuthController::class, 'logout'])->name('logout');
});

Route::get('/offers/{serviceRequest}/view', [PublicController::class, 'viewOffers'])->name('offers.view');

Route::get('/inspector/register', [InspectorRegisterController::class, 'show'])->name('gutachter.register');
Route::post('/inspector/register', [InspectorRegisterController::class, 'store'])->middleware('throttle:10,10')->name('gutachter.register.store');

Route::get('/inspector/verify-email/{inspector}/confirm', [InspectorRegisterController::class, 'confirmEmail'])
    ->middleware('signed')->name('inspector.verification.verify');
Route::get('/inspector/invite/{serviceRequest}/accept', [InspectorRegisterController::class, 'acceptInvite'])
    ->middleware('signed')->name('inspector.invite.accept');

// Guest offer submission — no inspector account needed yet. Gated by a
// session flag set only after the signed invite link above is visited, not
// by a signature of their own, since the form itself has no query params.
Route::get('/inspector/invite/{serviceRequest}/offer', [GuestOfferController::class, 'create'])
    ->name('inspector.guest-offer.create');
Route::post('/inspector/invite/{serviceRequest}/offer', [GuestOfferController::class, 'store'])
    ->name('inspector.guest-offer.store');

Route::get('/inspector/requests/{request}/direct/{inspector}', [InspectorAreaController::class, 'signedRequest'])
    ->middleware('signed')->name('inspector.requests.signed');

Route::middleware(['auth:inspector', EnsureInspectorOnboardingComplete::class])->prefix('inspector')->name('inspector.')->group(function () {
    Route::get('/verify-email', [InspectorAreaController::class, 'verificationNotice'])->name('verification.notice');
    Route::post('/verify-email/resend', [InspectorAreaController::class, 'resendVerification'])->name('verification.resend');
    Route::get('/complete-profile', [InspectorAreaController::class, 'onboardingProfile'])->name('onboarding.profile');
    Route::post('/complete-profile', [InspectorAreaController::class, 'storeOnboardingProfile'])->name('onboarding.profile.store');

    Route::get('/', [InspectorAreaController::class, 'dashboard'])->name('dashboard');
    Route::get('/requests', [InspectorAreaController::class, 'requests'])->name('requests');
    Route::get('/requests/{serviceRequest}', [InspectorAreaController::class, 'requestDetail'])->name('requests.show');
    Route::get('/requests/{serviceRequest}/offer', [InspectorAreaController::class, 'offerForm'])->name('requests.offer');
    Route::post('/requests/{serviceRequest}/offer', [InspectorAreaController::class, 'storeOffer'])->name('requests.offer.store');
    Route::get('/requests/{serviceRequest}/offer/edit', [InspectorAreaController::class, 'editOfferForm'])->name('requests.offer.edit');
    Route::put('/requests/{serviceRequest}/offer', [InspectorAreaController::class, 'updateOffer'])->name('requests.offer.update');
    Route::get('/offers', [InspectorAreaController::class, 'offers'])->name('offers');
    Route::get('/jobs', [InspectorAreaController::class, 'jobs'])->name('jobs');
    Route::get('/jobs/{booking}', [InspectorAreaController::class, 'jobDetail'])->name('jobs.show');
    Route::post('/jobs/{booking}/complete', [InspectorAreaController::class, 'completeJob'])->name('jobs.complete');
    Route::get('/service-areas', [InspectorAreaController::class, 'serviceAreas'])->name('service-areas');
    Route::post('/service-areas', [InspectorAreaController::class, 'storeServiceArea'])->name('service-areas.store');
    Route::delete('/service-areas/{area}', [InspectorAreaController::class, 'deleteServiceArea'])->name('service-areas.delete');
    Route::get('/invoices', [InspectorAreaController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{invoice}/download', [InspectorAreaController::class, 'downloadInvoice'])->name('invoices.download');
    Route::get('/profile', [InspectorAreaController::class, 'profile'])->name('profile');
    Route::post('/profile', [InspectorAreaController::class, 'updateProfile'])->name('profile.update');
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
    Route::get('/requests', [AdminController::class, 'requests'])->name('requests');
    Route::get('/requests/{serviceRequest}', [AdminController::class, 'requestDetail'])->name('requests.show');
    Route::post('/requests/{serviceRequest}/invite-provider', [AdminController::class, 'inviteProvider'])->name('requests.invite');
    Route::get('/offers', [AdminController::class, 'offers'])->name('offers');
    Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
    Route::get('/bookings/{booking}', [AdminController::class, 'bookingDetail'])->name('bookings.show');
    Route::post('/bookings/{booking}/confirm', [AdminController::class, 'confirmBooking'])->name('bookings.confirm');
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    Route::get('/commissions', [AdminController::class, 'commissions'])->name('commissions');
    Route::get('/inspectors', [AdminController::class, 'inspectors'])->name('inspectors');
    Route::get('/inspectors/import', [AdminController::class, 'importForm'])->name('inspectors.import');
    Route::post('/inspectors/import/preview', [AdminController::class, 'importPreview'])->name('inspectors.import.preview');
    Route::post('/inspectors/import', [AdminController::class, 'importStore'])->name('inspectors.import.store');
    Route::get('/inspectors/{inspector}', [AdminController::class, 'inspectorDetail'])->name('inspectors.show');
    Route::post('/inspectors/{inspector}/status', [AdminController::class, 'toggleInspector'])->name('inspectors.toggle');
    Route::post('/inspectors/{inspector}/approve', [AdminController::class, 'approveInspector'])->name('inspectors.approve');
    Route::post('/inspectors/{inspector}/verified', [AdminController::class, 'toggleVerified'])->name('inspectors.verified.toggle');
    Route::get('/wallets', [AdminController::class, 'wallets'])->name('wallets');
    Route::get('/payouts', [AdminController::class, 'payouts'])->name('payouts');
    Route::post('/payouts/{payout}/paid', [AdminController::class, 'markPayoutPaid'])->name('payouts.paid');
    Route::get('/invoices', [AdminController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{invoice}/download', [AdminController::class, 'downloadInvoice'])->name('invoices.download');
    Route::get('/reviews', [AdminController::class, 'reviews'])->name('reviews');
    Route::post('/reviews/{review}/published', [AdminController::class, 'togglePublished'])->name('reviews.published.toggle');
    Route::get('/customers', [AdminController::class, 'customers'])->name('customers');
    Route::get('/customers/{customer}', [AdminController::class, 'customerDetail'])->name('customers.show');
    Route::get('/services', [AdminController::class, 'services'])->name('services');
    Route::post('/categories/{category}/status', [AdminController::class, 'toggleCategory'])->name('categories.toggle');
    Route::post('/categories/{category}/service-types', [AdminController::class, 'storeServiceType'])->name('service-types.store');
    Route::post('/service-types/{serviceType}', [AdminController::class, 'updateServiceType'])->name('service-types.update');
    Route::delete('/service-types/{serviceType}', [AdminController::class, 'destroyServiceType'])->name('service-types.destroy');
    Route::get('/service-types/{serviceType}/fields', [AdminController::class, 'serviceTypeFields'])->name('service-types.fields');
    Route::post('/service-types/{serviceType}/fields', [AdminController::class, 'storeServiceTypeField'])->name('service-types.fields.store');
    Route::delete('/service-types/{serviceType}/fields/{field}', [AdminController::class, 'destroyServiceTypeField'])->name('service-types.fields.destroy');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
});

/*
|--------------------------------------------------------------------------
| Legacy German URLs — redirect to the new English routes.
| These paths were already live/emailed to real users; keep them working.
|--------------------------------------------------------------------------
*/
Route::get('/kfz-gutachten/{slug}', fn (string $slug) => redirect("/vehicle-reports/{$slug}", 301))->where('slug', '.*');
Route::redirect('/kfz-gutachten', '/vehicle-reports', 301);
Route::redirect('/so-funktionierts', '/how-it-works', 301);
Route::redirect('/fuer-gutachter', '/for-inspectors', 301);
Route::redirect('/ueber-uns', '/about', 301);
Route::redirect('/kontakt', '/contact', 301);
Route::redirect('/preise', '/pricing', 301);
Route::redirect('/bewertungen', '/reviews', 301);
Route::redirect('/impressum', '/imprint', 301);
Route::redirect('/datenschutz', '/privacy', 301);
Route::redirect('/agb', '/terms', 301);
Route::redirect('/cookie-richtlinie', '/cookie-policy', 301);
Route::get('/demnaechst/{slug}/interesse', fn (string $slug) => redirect("/coming-soon/{$slug}/interest", 301))->where('slug', '.*');
Route::get('/demnaechst/{slug}', fn (string $slug) => redirect("/coming-soon/{$slug}", 301))->where('slug', '.*');
Route::get('/anfrage/bestaetigung/{number}', fn (string $number) => redirect("/request/confirmation/{$number}", 301))->where('number', '.*');
Route::redirect('/anfrage', '/request', 301);
Route::redirect('/registrieren/gutachter', '/inspector/register', 301);
Route::get('/konto/{path?}', fn (?string $path = null) => redirect('/account'.($path ? '/'.$path : ''), 301))->where('path', '.*');
Route::get('/gutachter/{path?}', fn (?string $path = null) => redirect('/inspector'.($path ? '/'.$path : ''), 301))->where('path', '.*');

require __DIR__.'/settings.php';
