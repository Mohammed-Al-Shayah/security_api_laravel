<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inspector;
use App\Models\Patrol;
use App\Models\Incident;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InspectorMobileController extends Controller
{
    /**
     * Basic profile + today's patrols + quick stats for the inspector.
     * GET /api/inspector/home
     */
    public function home(Request $request)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'INSPECTOR') {
            return response()->json([
                'message' => 'Only inspector users can access this endpoint.',
            ], 403);
        }

        /** @var Inspector $inspector */
        $inspector = Inspector::with(['user', 'projects'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $today = Carbon::today()->toDateString();

        $todayPatrols = Patrol::with(['project:id,name,location'])
            ->where('inspector_id', $inspector->id)
            ->whereDate('date', $today)
            ->get();

        $recentIncidentsCount = Incident::where('reporter_id', $user->id)
            ->whereDate('created_at', '>=', Carbon::today()->subDays(7))
            ->count();

        return response()->json([
            'data' => [
                'id'           => $inspector->id,
                'name'         => $inspector->user->name,
                'email'        => $inspector->user->email,
                'phone'        => $inspector->user->phone,
                'employee_code'=> $inspector->employee_code,
                'status'       => $inspector->status,

                'projects' => $inspector->projects->map(function ($project) {
                    return [
                        'id'       => $project->id,
                        'name'     => $project->name,
                        'location' => $project->location,
                    ];
                })->values(),

                'today_patrols' => $todayPatrols->map(function (Patrol $patrol) {
                    return [
                        'id'          => $patrol->id,
                        'date'        => $patrol->date,
                        'start_time'  => $patrol->start_time,
                        'end_time'    => $patrol->end_time,
                        'status'      => $patrol->status,
                        'project'     => $patrol->project ? [
                            'id'       => $patrol->project->id,
                            'name'     => $patrol->project->name,
                            'location' => $patrol->project->location,
                        ] : null,
                    ];
                })->values(),

                'stats' => [
                    'recent_incidents_last_7_days' => $recentIncidentsCount,
                ],
            ],
        ]);
    }

    /**
     * Inspector patrols list with optional date / scope filters.
     * GET /api/inspector/patrols?date=YYYY-MM-DD
     */
    public function patrols(Request $request)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'INSPECTOR') {
            return response()->json([
                'message' => 'Only inspector users can access this endpoint.',
            ], 403);
        }

        $inspector = Inspector::where('user_id', $user->id)->firstOrFail();

        $dateParam = $request->get('date');
        $query = Patrol::with(['project:id,name,location', 'securityGuard.user'])
            ->where('inspector_id', $inspector->id);

        if ($dateParam) {
            $query->whereDate('date', $dateParam);
        }

        $patrols = $query->orderByDesc('date')->get();

        return response()->json([
            'data' => $patrols->map(function (Patrol $patrol) {
                return [
                    'id'          => $patrol->id,
                    'date'        => $patrol->date,
                    'start_time'  => $patrol->start_time,
                    'end_time'    => $patrol->end_time,
                    'status'      => $patrol->status,
                    'rating'      => $patrol->rating,
                    'notes'       => $patrol->notes,
                    'project'     => $patrol->project ? [
                        'id'       => $patrol->project->id,
                        'name'     => $patrol->project->name,
                        'location' => $patrol->project->location,
                    ] : null,
                    'guard'       => $patrol->securityGuard ? [
                        'id'    => $patrol->securityGuard->id,
                        'name'  => optional($patrol->securityGuard->user)->name,
                        'phone' => optional($patrol->securityGuard->user)->phone,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * Incidents reported by this inspector.
     * GET /api/inspector/incidents
     */
    public function incidents(Request $request)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'INSPECTOR') {
            return response()->json([
                'message' => 'Only inspector users can access this endpoint.',
            ], 403);
        }

        $incidents = Incident::with(['project:id,name,location', 'type:id,name'])
            ->where('reporter_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $incidents->map(function (Incident $incident) {
                return [
                    'id'          => $incident->id,
                    'title'       => $incident->title,
                    'description' => $incident->description,
                    'status'      => $incident->status,
                    'occurred_at' => $incident->occurred_at,
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

    /**
     * Projects assigned to this inspector.
     * GET /api/inspector/projects
     */
    public function projects(Request $request)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'INSPECTOR') {
            return response()->json([
                'message' => 'Only inspector users can access this endpoint.',
            ], 403);
        }

        $inspector = Inspector::with('projects')->where('user_id', $user->id)->firstOrFail();

        return response()->json([
            'data' => $inspector->projects->map(function ($project) {
                return [
                    'id'       => $project->id,
                    'name'     => $project->name,
                    'location' => $project->location,
                ];
            }),
        ]);
    }
}
