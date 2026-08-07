<?php

namespace App\Http\Middleware;

use App\Models\AppNotification;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $inspector = Auth::guard('inspector')->user();
        $admin = Auth::guard('admin')->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'inspector' => $inspector ? [
                    'id' => $inspector->id,
                    'name' => $inspector->name,
                    'company' => $inspector->company_name,
                    'email' => $inspector->email,
                    'avatar' => $inspector->avatar_key ? ['key' => $inspector->avatar_key, ...config('avatars.'.$inspector->avatar_key, [])] : null,
                ] : null,
                'admin' => $admin ? [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                ] : null,
            ],
            'notifications' => fn () => $this->notifications($request, $inspector),
            'navCategories' => fn () => Cache::remember('nav_categories', now()->addHour(), fn () => ServiceCategory::orderBy('sort_order')
                ->get(['id', 'name', 'slug', 'icon', 'is_active'])
                ->toArray()
            ),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * @return array{count: int, items: array<int, array<string, mixed>>}
     */
    private function notifications(Request $request, $inspector): array
    {
        $notifiable = $inspector ?: $request->user();

        if (! $notifiable) {
            return ['count' => 0, 'items' => []];
        }

        $query = AppNotification::where('notifiable_type', $notifiable->getMorphClass())
            ->where('notifiable_id', $notifiable->getKey());

        return [
            'count' => (clone $query)->whereNull('read_at')->count(),
            'items' => (clone $query)->latest()->take(8)->get()
                ->map(fn ($n) => [
                    'id' => $n->id,
                    'title' => $n->title,
                    'body' => $n->body,
                    'link' => $n->link,
                    'read' => $n->read_at !== null,
                    'date' => $n->created_at->diffForHumans(),
                ])->all(),
        ];
    }
}
