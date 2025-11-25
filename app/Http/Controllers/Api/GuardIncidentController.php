<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\IncidentAttachment;
use Illuminate\Http\Request;

class GuardIncidentController extends Controller
{
    /**
     * إنشاء بلاغ حادث من الحارس
     * POST /api/guard/incidents
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'GUARD') {
            return response()->json([
                'message' => 'Only guards can create incidents.'
            ], 403);
        }

        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'type_id'    => 'required|exists:incident_types,id',
            'title'      => 'required|string|max:255',
            'description'=> 'nullable|string',
            'occurred_at'=> 'required|date',
        ]);

        $incident = Incident::create([
            'project_id' => $data['project_id'],
            'type_id'    => $data['type_id'],
            'title'      => $data['title'],
            'description'=> $data['description'] ?? null,
            'occurred_at'=> $data['occurred_at'],
            'status'     => 'OPEN',
            'reporter_id'=> $user->id,
        ]);

        return response()->json([
            'message' => 'Incident reported successfully.',
            'data'    => $incident
        ], 201);
    }

    /**
     * رفع مرفق لحادث
     * POST /api/guard/incidents/{id}/attachments
     */
    public function uploadAttachment(Request $request, Incident $incident)
    {
        $user = $request->user();

        if ($user->id !== $incident->reporter_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4,pdf|max:20480'
        ]);

        $path = $request->file('file')->store('incidents', 'public');

        $attachment = IncidentAttachment::create([
            'incident_id' => $incident->id,
            'file_path'   => $path,
        ]);

        return response()->json([
            'message' => 'Attachment uploaded.',
            'data'    => $attachment
        ], 201);
    }
}
