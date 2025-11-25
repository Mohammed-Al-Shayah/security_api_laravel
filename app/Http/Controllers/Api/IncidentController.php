<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    /**
     * قائمة الحوادث + فلترة
     *
     * Query params (كلهم اختياريين):
     * - project_id
     * - type_id
     * - status (OPEN, IN_PROGRESS, RESOLVED)
     * - date_from (YYYY-MM-DD)
     * - date_to   (YYYY-MM-DD)
     * - search    (يبحث في العنوان والوصف)
     * - per_page  (افتراضي 20)
     */
    public function index(Request $request)
    {
        $query = Incident::query()
            ->with(['project', 'type', 'reporter']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->date_to);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = $request->integer('per_page', 20);

        $incidents = $query
            ->orderByDesc('occurred_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $incidents,
        ]);
    }

    /**
     * إنشاء حادث جديد
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'   => 'required|exists:projects,id',
            'type_id'      => 'required|exists:incident_types,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'occurred_at'  => 'required|date', // يدعم ISO من Postman
        ]);

        $data['reporter_id'] = $request->user()->id;
        $data['status']      = 'OPEN';

        $incident = Incident::create($data)->load(['project', 'type', 'reporter']);

        return response()->json([
            'success' => true,
            'message' => 'Incident created successfully.',
            'data'    => $incident,
        ], 201);
    }

    /**
     * عرض حادث واحد بالتفاصيل
     */
    public function show(Incident $incident)
    {
        $incident->load(['project', 'type', 'reporter', 'attachments']);

        return response()->json([
            'success' => true,
            'data'    => $incident,
        ]);
    }

    /**
     * تعديل حادث
     */
    public function update(Request $request, Incident $incident)
    {
        $data = $request->validate([
            'project_id'   => 'sometimes|exists:projects,id',
            'type_id'      => 'sometimes|exists:incident_types,id',
            'title'        => 'sometimes|string|max:255',
            'description'  => 'sometimes|nullable|string',
            'occurred_at'  => 'sometimes|date',
            'status'       => 'sometimes|in:OPEN,IN_PROGRESS,RESOLVED',
        ]);

        $incident->update($data);

        $incident->load(['project', 'type', 'reporter', 'attachments']);

        return response()->json([
            'success' => true,
            'message' => 'Incident updated successfully.',
            'data'    => $incident,
        ]);
    }
}
