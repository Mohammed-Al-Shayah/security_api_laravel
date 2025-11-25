<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guard;
use App\Models\Inspector;
use App\Models\Project;
use App\Models\Shift;
use App\Models\Patrol;
use App\Models\Incident;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $today = Carbon::today();

        // أرقام أساسية
        $totalGuards      = Guard::count();
        $activeGuards     = Guard::where('status', 'ACTIVE')->count();

        $totalInspectors  = Inspector::count();
        $totalProjects    = Project::count();
        $activeProjects   = Project::where('status', 'ACTIVE')->count();
        $suspendedProjects = Project::where('status', 'SUSPENDED')->count();

        // شفتات اليوم
        $todayShifts = Shift::whereDate('date', $today)->count();
        $todayStartedShifts = Shift::whereDate('date', $today)
            ->where('status', 'STARTED')
            ->count();
        $todayFinishedShifts = Shift::whereDate('date', $today)
            ->where('status', 'FINISHED')
            ->count();

        // دوريات اليوم
        $todayPatrols = Patrol::whereDate('start_time', $today)->count();

        // الحوادث
        $openIncidents = Incident::where('status', 'OPEN')->count();
        $inProgressIncidents = Incident::where('status', 'IN_PROGRESS')->count();
        $resolvedIncidents = Incident::where('status', 'RESOLVED')->count();

        // عدد كل الحوادث التي حدثت اليوم
        $todayIncidents = Incident::whereDate('occurred_at', $today)->count();

        // أكثر مشروع فيه حوادث مفتوحة
        $topIncidentProject = Incident::selectRaw('project_id, COUNT(*) as total')
            ->where('status', 'OPEN')
            ->groupBy('project_id')
            ->orderByDesc('total')
            ->with('project:id,name')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'counts' => [
                    'guards' => [
                        'total'  => $totalGuards,
                        'active' => $activeGuards,
                    ],
                    'inspectors' => [
                        'total' => $totalInspectors,
                    ],
                    'projects' => [
                        'total'      => $totalProjects,
                        'active'     => $activeProjects,
                        'suspended'  => $suspendedProjects,
                    ],
                ],

                'today' => [
                    'date' => $today->toDateString(),
                    'shifts' => [
                        'total'    => $todayShifts,
                        'started'  => $todayStartedShifts,
                        'finished' => $todayFinishedShifts,
                    ],
                    'patrols' => [
                        'total' => $todayPatrols,
                    ],
                    'incidents' => [
                        'total' => $todayIncidents,
                    ],
                ],

                'incidents' => [
                    'open'        => $openIncidents,
                    'in_progress' => $inProgressIncidents,
                    'resolved'    => $resolvedIncidents,
                    'top_project' => $topIncidentProject
                        ? [
                            'project_id'     => $topIncidentProject->project_id,
                            'project_name'   => optional($topIncidentProject->project)->name,
                            'open_incidents' => $topIncidentProject->total,
                        ]
                        : null,
                ],
            ],
        ]);
    }
}
