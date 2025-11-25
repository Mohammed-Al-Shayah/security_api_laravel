<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guard;
use App\Models\Patrol;
use Illuminate\Http\Request;

class GuardPatrolController extends Controller
{
    /**
     * Helper: رجّع الجارد المرتبط باليوزر الحالي
     */
    protected function getGuardFromRequest(Request $request): ?Guard
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        return Guard::where('user_id', $user->id)->first();
    }

    /**
     * GET /api/guard/patrols
     * رجوع كل الدوريات الخاصة بالجارد الحالي
     */
    public function index(Request $request)
    {
        $guard = $this->getGuardFromRequest($request);

        if (! $guard) {
            return response()->json([
                'message' => 'This user is not linked to any guard profile.',
            ], 403);
        }

        $patrols = Patrol::with('project')
            ->where('guard_id', $guard->id)
            ->orderByDesc('start_time')
            ->get();

        return response()->json([
            'data' => $patrols,
        ]);
    }

    /**
     * GET /api/guard/patrols/{patrol}
     * عرض دورية واحدة للجارد الحالي
     */
    public function show(Request $request, Patrol $patrol)
    {
        $guard = $this->getGuardFromRequest($request);

        if (! $guard) {
            return response()->json([
                'message' => 'This user is not linked to any guard profile.',
            ], 403);
        }

        // تأكد إن هاي الدورية فعلاً للجارد الحالي
        if ($patrol->guard_id !== $guard->id) {
            return response()->json([
                'message' => 'You are not allowed to view this patrol.',
            ], 403);
        }

        $patrol->load('project');

        return response()->json([
            'data' => $patrol,
        ]);
    }

    /**
     * POST /api/guard/patrols
     * إنشاء دورية جديدة للجارد الحالي
     */
    public function store(Request $request)
    {
        $guard = $this->getGuardFromRequest($request);

        if (! $guard) {
            return response()->json([
                'message' => 'This user is not linked to any guard profile.',
            ], 403);
        }

        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after_or_equal:start_time',
            'rating'     => 'nullable|integer|min:1|max:5',
            'notes'      => 'nullable|string',
        ]);

        $patrol = Patrol::create([
            'inspector_id' => null,               // حالياً مش بنستخدمه من الموبايل
            'project_id'   => $data['project_id'],
            'guard_id'     => $guard->id,
            'start_time'   => $data['start_time'],
            'end_time'     => $data['end_time'],
            'rating'       => $data['rating'] ?? null,
            'notes'        => $data['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Patrol created successfully.',
            'data'    => $patrol,
        ], 201);
    }
}
