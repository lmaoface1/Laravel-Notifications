<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /api/notifications — get all notifications for logged in user
    public function index(Request $request)
    {
        return response()->json([
            'notifications' => $request->user()->notifications,
            'unread_count'  => $request->user()->unreadNotifications->count(),
        ], 200);
    }

    // POST /api/notifications/{id}/read — mark one as read
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read'], 200);
    }

    // POST /api/notifications/read-all — mark all as read
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read'], 200);
    }
}