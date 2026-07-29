<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAllRead(Request $request): RedirectResponse
    {
        $notifiable = Auth::guard('inspector')->user() ?: $request->user();

        AppNotification::where('notifiable_type', $notifiable->getMorphClass())
            ->where('notifiable_id', $notifiable->getKey())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }
}
