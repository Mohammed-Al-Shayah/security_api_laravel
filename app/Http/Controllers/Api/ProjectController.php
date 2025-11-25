<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponseTrait;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $projects = Project::with('manager')->GET();

        return $this->success($projects, 'Projects list.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'location'   => 'nullable|string',
            'lat'        => 'nullable|numeric',
            'lng'        => 'nullable|numeric',
            'manager_id' => 'required|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
        ]);

        $project = Project::create($data);

        return $this->success($project, 'Project created successfully.', 201);
    }

    public function show(Project $project)
    {
        $project->load(['manager', 'guards']);

        return $this->success($project, 'Project details.');
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'location'   => 'nullable|string',
            'lat'        => 'nullable|numeric',
            'lng'        => 'nullable|numeric',
            'manager_id' => 'sometimes|exists:users,id',
            'status'     => 'sometimes|in:ACTIVE,SUSPENDED,FINISHED',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
        ]);

        $project->update($data);

        return $this->success($project, 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return $this->success(null, 'Project deleted successfully.');
    }

    public function assignGuards(Request $request, Project $project)
    {
        $data = $request->validate([
            'guard_ids'     => 'required|array',
            'guard_ids.*'   => 'exists:guards,id',
            'assigned_from' => 'nullable|date',
            'assigned_to'   => 'nullable|date',
        ]);

        $syncData = [];
        foreach ($data['guard_ids'] as $guardId) {
            $syncData[$guardId] = [
                'assigned_from' => $data['assigned_from'] ?? now()->toDateString(),
                'assigned_to'   => $data['assigned_to'] ?? null,
            ];
        }

        $project->guards()->syncWithoutDetaching($syncData);

        return $this->success(null, 'Guards assigned successfully.');
    }

    public function guards(Project $project)
    {
        $guards = $project->guards()->with('user')->get();

        return $this->success($guards, 'Project guards list.');
    }
}
