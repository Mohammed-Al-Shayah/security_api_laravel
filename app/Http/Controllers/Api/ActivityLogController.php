<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * عرض جميع سجلات النشاط
     * ADMIN فقط
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        // فلترة حسب المستخدم
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // فلترة حسب Model
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // فلترة حسب Action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        return response()->json([
            'success' => true,
            'message' => 'Activity logs fetched successfully.',
            'data'    => $query->paginate(30),
        ]);
    }

    /**
     * عرض سجل واحد
     */
    public function show(ActivityLog $log)
    {
        return response()->json([
            'success' => true,
            'message' => 'Activity log details.',
            'data'    => $log->load('user'),
        ]);
    }
}
