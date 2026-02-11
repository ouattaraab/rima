<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::forUser($request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, string $id)
    {
        $notification = Notification::forUser($request->user()->id)->findOrFail($id);

        $notification->update(['read_at' => now()]);

        return back()->with('success', 'Notification marquee comme lue');
    }

    public function markAllRead(Request $request)
    {
        Notification::forUser($request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return back()->with('success', 'Toutes les notifications ont ete marquees comme lues');
    }
}
