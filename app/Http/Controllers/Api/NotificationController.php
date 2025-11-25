<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * عرض إشعارات المستخدم الحالي (مع إمكانية فلترة الـ unread فقط)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = $user->notifications()->latest();

        if ($request->boolean('only_unread')) {
            $query = $user->unreadNotifications()->latest();
        }

        $notifications = $query->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Notifications fetched successfully.',
            'data'    => $notifications,
        ]);
    }

    /**
     * تعليم إشعار واحد كمقروء
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * تعليم كل الإشعارات كمقروءة
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }
}
