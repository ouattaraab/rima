<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::forUser($request->user()->id)
            ->orderByDesc('created_at')
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $notifications->map(fn(Notification $n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'data' => $n->data,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at,
            ]),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'total_pages' => $notifications->lastPage(),
                'total_items' => $notifications->total(),
                'items_per_page' => $notifications->perPage(),
            ],
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = Notification::forUser($request->user()->id)->findOrFail($id);

        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marquee comme lue',
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        Notification::forUser($request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Toutes les notifications ont ete marquees comme lues',
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::forUser($request->user()->id)
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }
}
