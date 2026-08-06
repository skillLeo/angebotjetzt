<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use App\Models\Booking;
use App\Models\CategoryInterestSignal;
use App\Models\Inspector;
use App\Models\Offer;
use App\Models\Review;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\User;
use App\Support\SafeMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class PublicController extends Controller
{
    public function home(): Response
    {
        $data = (function () {
            $portraits = config('media.portraits');
            $vehicles = config('media.vehicles');

            $providers = Inspector::query()
                ->where('is_active', true)
                ->where('is_verified', true)
                ->withCount(['reviews' => fn ($q) => $q->where('is_published', true)])
                ->withAvg(['reviews' => fn ($q) => $q->where('is_published', true)], 'rating')
                ->withCount(['bookings' => fn ($q) => $q->whereIn('status', ['confirmed', 'completed_by_inspector'])])
                ->orderByDesc('reviews_count')
                ->take(9)
                ->get()
                ->values()
                ->map(fn ($i, $idx) => [
                    'name' => $i->name,
                    'city' => $i->city,
                    'reviews' => $i->reviews_count,
                    'rating' => $i->reviews_avg_rating ? round((float) $i->reviews_avg_rating, 1) : null,
                    'jobs' => $i->bookings_count,
                    'since' => $i->member_since?->locale('de')->translatedFormat('F Y'),
                    'photo' => $portraits[$idx % count($portraits)],
                ]);

            $recentRequests = ServiceRequest::query()
                ->with('serviceType:id,name')
                ->whereIn('status', ['offers_received', 'completed', 'accepted'])
                ->latest()
                ->take(10)
                ->get()
                ->values()
                ->map(fn ($r, $idx) => [
                    'title' => $r->vehicle_make.' '.$r->vehicle_model,
                    'service' => $r->serviceType->name,
                    'ort' => $r->ort,
                    'plz' => $r->plz,
                    'price' => $r->offers()->min('price_cents'),
                    'photo' => $vehicles[$idx % count($vehicles)],
                ]);

            $reviews = Review::query()
                ->with(['user:id,name', 'inspector:id,city', 'booking.request.serviceType:id,name'])
                ->where('is_published', true)
                ->where('rating', '>=', 4)
                ->latest()
                ->take(8)
                ->get()
                ->map(fn ($rev) => [
                    'name' => $rev->user->name,
                    'text' => $rev->comment,
                    'rating' => $rev->rating,
                    'service' => $rev->booking?->request?->serviceType?->name,
                    'city' => $rev->inspector?->city,
                ]);

            $cityCounts = Inspector::query()
                ->where('is_active', true)
                ->selectRaw('city, COUNT(*) as count')
                ->groupBy('city')
                ->pluck('count', 'city');

            return [
                'stats' => [
                    'bookings' => Booking::count(),
                    'inspectors' => Inspector::where('is_active', true)->count(),
                    'avgOffers' => round(Offer::count() / max(1, ServiceRequest::count()), 1),
                    'avgResponseHours' => 3,
                ],
                'providers' => $providers,
                'recentRequests' => $recentRequests,
                'reviews' => $reviews,
                'cityCounts' => $cityCounts,
                'totalReviews' => Review::where('is_published', true)->count(),
            ];
        })();

        return Inertia::render('Home', [
            'categories' => $this->categories(),
            'serviceTypes' => $this->serviceTypes(),
            ...$data,
        ]);
    }

    public function requestStart(): Response
    {
        return Inertia::render('public/RequestStart', [
            'categories' => $this->categories(),
            'serviceTypes' => $this->serviceTypes(),
        ]);
    }

    public function category(): Response
    {
        return Inertia::render('public/Category', [
            'serviceTypes' => $this->serviceTypes(),
            'inspectorCount' => Inspector::where('is_active', true)->count(),
        ]);
    }

    public function serviceType(ServiceType $serviceType): Response
    {
        $serviceType->loadMissing('category:id,name,slug');

        return Inertia::render('public/ServiceType', [
            'serviceType' => [
                'name' => $serviceType->name,
                'slug' => $serviceType->slug,
                'description' => $serviceType->description,
                'image' => $serviceType->image_url ?? config('media.hero.inspection'),
                'category' => $serviceType->category ? [
                    'name' => $serviceType->category->name,
                    'slug' => $serviceType->category->slug,
                ] : null,
            ],
            'others' => $this->serviceTypes()->where('slug', '!=', $serviceType->slug)->values(),
        ]);
    }

    public function howItWorks(): Response
    {
        return Inertia::render('public/HowItWorks');
    }

    public function forInspectors(): Response
    {
        return Inertia::render('public/ForInspectors', [
            'stats' => [
                'inspectors' => Inspector::where('is_active', true)->count(),
                'requests' => ServiceRequest::count(),
            ],
        ]);
    }

    public function about(): Response
    {
        return Inertia::render('public/About');
    }

    public function contact(): Response
    {
        return Inertia::render('public/Contact');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'subject' => ['required', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        SafeMailer::send(fn () => Mail::to(config('mail.from.address'))->queue(new ContactFormMail($data)));

        return back()->with('success', 'Vielen Dank für Ihre Nachricht. Wir melden uns innerhalb von 24 Stunden bei Ihnen.');
    }

    public function pricing(): Response
    {
        return Inertia::render('public/Pricing');
    }

    public function faq(): Response
    {
        return Inertia::render('public/Faq');
    }

    public function reviews(): Response
    {
        return Inertia::render('public/Reviews', [
            'reviews' => Review::with(['user:id,name', 'inspector:id,name,city', 'booking.request.serviceType:id,name'])
                ->where('is_published', true)
                ->latest()
                ->paginate(12)
                ->through(fn ($rev) => [
                    'name' => $rev->user->name,
                    'inspector' => $rev->inspector?->name,
                    'city' => $rev->inspector?->city,
                    'service' => $rev->booking?->request?->serviceType?->name,
                    'text' => $rev->comment,
                    'rating' => $rev->rating,
                    'date' => $rev->created_at->format('d.m.Y'),
                ]),
        ]);
    }

    public function imprint(): Response
    {
        return Inertia::render('public/legal/Imprint');
    }

    public function privacy(): Response
    {
        return Inertia::render('public/legal/Privacy');
    }

    public function terms(): Response
    {
        return Inertia::render('public/legal/Terms');
    }

    public function cookies(): Response
    {
        return Inertia::render('public/legal/Cookies');
    }

    /**
     * Unauthenticated landing point for a customer clicking an offer
     * notification email. Guarded routes like konto.requests.offers always
     * bounce anonymous visitors to /login regardless of whether they even
     * have an account yet — wrong for a guest who submitted their request
     * without registering. This decides the right next step instead: already
     * signed in → straight to the offers; an account exists for this
     * request's email → log in; otherwise → register, with that email
     * pre-filled.
     */
    public function viewOffers(ServiceRequest $serviceRequest): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('konto.requests.offers', $serviceRequest->id);
        }

        if (User::where('email', $serviceRequest->contact_email)->exists()) {
            return redirect()->route('login');
        }

        return redirect()->route('register', ['email' => $serviceRequest->contact_email]);
    }

    public function comingSoon(ServiceCategory $category): Response
    {
        abort_if($category->is_active, 404);

        return Inertia::render('public/ComingSoon', [
            'category' => $category->only(['name', 'slug', 'icon', 'description']),
        ]);
    }

    public function registerInterest(Request $request, ServiceCategory $category): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:190']]);

        CategoryInterestSignal::firstOrCreate(
            ['service_category_id' => $category->id, 'email' => $data['email']],
            ['ip' => $request->ip()]
        );

        return back()->with('success', 'Vielen Dank! Wir benachrichtigen Sie, sobald diese Kategorie verfügbar ist.');
    }

    private function categories()
    {
        return ServiceCategory::orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'icon', 'description', 'is_active']);
    }

    private function serviceTypes()
    {
        return ServiceType::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'description', 'image_url', 'service_category_id'])
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'description' => $t->description,
                'image' => $t->image_url ?? config('media.hero.inspection'),
                'categoryId' => $t->service_category_id,
            ]);
    }
}
