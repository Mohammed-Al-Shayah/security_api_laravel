<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponseTrait;
use App\Models\Shift;
use App\Models\Guard;
use App\Models\Project;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    use ApiResponseTrait;

    public function show(Shift $shift)
    {
        $shift->load(['project', 'securityGuard.user']);

        return $this->success($shift, 'Shift details.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'guard_id'   => 'required|exists:guards,id',
            'date'       => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
        ]);

        $shift = Shift::create($data);

        return $this->success($shift, 'Shift created successfully.', 201);
    }

    public function update(Request $request, Shift $shift)
    {
        $data = $request->validate([
            'project_id' => 'sometimes|exists:projects,id',
            'guard_id'   => 'sometimes|exists:guards,id',
            'date'       => 'sometimes|date',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time'   => 'sometimes|date_format:H:i|after:start_time',
            'status'     => 'sometimes|in:PLANNED,STARTED,FINISHED,MISSED',
        ]);

        $shift->update($data);

        return $this->success($shift, 'Shift updated successfully.');
    }

    public function forGuard(Guard $guard, Request $request)
    {
        $query = $guard->shifts()->with('project');

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        $shifts = $query->orderBy('date', 'desc')->paginate(15);

        return $this->success($shifts, 'Guard shifts list.');
    }

    public function forProject(Project $project, Request $request)
    {
        $query = $project->shifts()->with('securityGuard.user');

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        $shifts = $query->orderBy('date', 'desc')->paginate(20);

        return $this->success($shifts, 'Project shifts list.');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();

        return $this->success(null, 'Shift deleted successfully.');
    }
}
