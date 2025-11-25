<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guard;
use App\Models\Shift;
use App\Models\Attendance;
use App\Models\Patrol;
use App\Models\Incident;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GuardMobileController extends Controller
{
    /**
     * رجع ملف الحارس + معلومات مفيدة للموبايل
     * GET /api/guard/me
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'GUARD') {
            return response()->json([
                'message' => 'Only guard users can access this endpoint.',
            ], 403);
        }

        /** @var Guard $guard */
        $guard = Guard::with(['user', 'projects'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $today = Carbon::today()->toDateString();

        $todayShift = Shift::where('guard_id', $guard->id)
            ->whereDate('date', $today)
            ->with('project:id,name,location')
            ->first();

        return response()->json([
            'data' => [
                'id'           => $guard->id,
                'name'         => $guard->user->name,
                'email'        => $guard->user->email,
                'phone'        => $guard->user->phone,
                'badge_number' => $guard->badge_number,
                'status'       => $guard->status,

                'projects' => $guard->projects->map(function ($project) {
                    return [
                        'id'       => $project->id,
                        'name'     => $project->name,
                        'location' => $project->location,
                    ];
                })->values(),

                'today_shift' => $todayShift ? [
                    'id'         => $todayShift->id,
                    'date'       => $todayShift->date,
                    'start_time' => $todayShift->start_time,
                    'end_time'   => $todayShift->end_time,
                    'status'     => $todayShift->status,
                    'project'    => $todayShift->project ? [
                        'id'       => $todayShift->project->id,
                        'name'     => $todayShift->project->name,
                        'location' => $todayShift->project->location,
                    ] : null,
                ] : null,
            ],
        ]);
    }

    /**
     * شيفتات الحارس
     * GET /api/guard/shifts?scope=today|upcoming|past&date=YYYY-MM-DD
     */
    public function shifts(Request $request)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'GUARD') {
            return response()->json(['message' => 'Only guard users can access this endpoint.'], 403);
        }

        $guard = Guard::where('user_id', $user->id)->firstOrFail();

        $scope = $request->get('scope', 'today'); // today | upcoming | past | all
        $dateParam = $request->get('date');
        $today = Carbon::today()->toDateString();

        $query = Shift::with('project:id,name,location')
            ->where('guard_id', $guard->id);

        if ($dateParam) {
            $query->whereDate('date', $dateParam);
        } else {
            if ($scope === 'today') {
                $query->whereDate('date', $today);
            } elseif ($scope === 'upcoming') {
                $query->whereDate('date', '>', $today);
            } elseif ($scope === 'past') {
                $query->whereDate('date', '<', $today);
            }
            // all = no extra date filter
        }

        $shifts = $query->orderBy('date')->get();

        return response()->json([
            'data' => $shifts->map(function (Shift $shift) {
                return [
                    'id'         => $shift->id,
                    'date'       => $shift->date,
                    'start_time' => $shift->start_time,
                    'end_time'   => $shift->end_time,
                    'status'     => $shift->status,
                    'project'    => $shift->project ? [
                        'id'       => $shift->project->id,
                        'name'     => $shift->project->name,
                        'location' => $shift->project->location,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * حضور الحارس (تاريخي)
     * GET /api/guard/attendance?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function attendance(Request $request)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'GUARD') {
            return response()->json(['message' => 'Only guard users can access this endpoint.'], 403);
        }

        $guard = Guard::where('user_id', $user->id)->firstOrFail();

        $from = $request->get('from');
        $to   = $request->get('to');

        $query = Attendance::with('shift.project:id,name,location')
            ->where('guard_id', $guard->id);

        if ($from) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('date', '<=', $to);
        }

        $records = $query->orderByDesc('date')->limit(30)->get();

        return response()->json([
            'data' => $records->map(function (Attendance $record) {
                return [
                    'id'          => $record->id,
                    'date'        => $record->date,
                    'check_in_at' => $record->check_in_at,
                    'check_out_at'=> $record->check_out_at,
                    'status'      => $record->status,
                    'project'     => $record->shift && $record->shift->project ? [
                        'id'       => $record->shift->project->id,
                        'name'     => $record->shift->project->name,
                        'location' => $record->shift->project->location,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * دوريات الحارس
     * GET /api/guard/patrols?date=YYYY-MM-DD
     */
    public function patrols(Request $request)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'GUARD') {
            return response()->json(['message' => 'Only guard users can access this endpoint.'], 403);
        }

        $guard = Guard::where('user_id', $user->id)->firstOrFail();

        $query = Patrol::with(['project:id,name,location', 'inspector.user:id,name'])
            ->where('guard_id', $guard->id);

        if ($request->filled('date')) {
            $query->whereDate('start_time', $request->get('date'));
        }

        $patrols = $query->orderByDesc('start_time')->get();

        return response()->json([
            'data' => $patrols->map(function (Patrol $patrol) {
                return [
                    'id'         => $patrol->id,
                    'start_time' => $patrol->start_time,
                    'end_time'   => $patrol->end_time,
                    'rating'     => $patrol->rating,
                    'notes'      => $patrol->notes,
                    'project'    => $patrol->project ? [
                        'id'       => $patrol->project->id,
                        'name'     => $patrol->project->name,
                        'location' => $patrol->project->location,
                    ] : null,
                    'inspector'  => $patrol->inspector && $patrol->inspector->user ? [
                        'id'   => $patrol->inspector->id,
                        'name' => $patrol->inspector->user->name,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * الحوادث اللي بلّغ عنها الحارس
     * GET /api/guard/incidents
     */
    public function incidents(Request $request)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'GUARD') {
            return response()->json(['message' => 'Only guard users can access this endpoint.'], 403);
        }

        // نفترض إن الـ incident عندك فيها عمود reporter_id بيربط على users.id
        $incidents = Incident::with(['project:id,name,location', 'type:id,name'])
            ->where('reporter_id', $user->id)
            ->orderByDesc('occurred_at')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $incidents->map(function (Incident $incident) {
                return [
                    'id'          => $incident->id,
                    'title'       => $incident->title,
                    'status'      => $incident->status,
                    'occurred_at' => $incident->occurred_at?->toIso8601String(),
                    'project'     => $incident->project ? [
                        'id'       => $incident->project->id,
                        'name'     => $incident->project->name,
                        'location' => $incident->project->location,
                    ] : null,
                    'type'        => $incident->type ? [
                        'id'   => $incident->type->id,
                        'name' => $incident->type->name,
                    ] : null,
                ];
            }),
        ]);
    }
}
