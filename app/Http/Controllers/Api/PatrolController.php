<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponseTrait;
use App\Models\Patrol;
use Illuminate\Http\Request;

class PatrolController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $query = Patrol::with(['inspector.user', 'project', 'securityGuard.user']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('start_time', $request->date);
        }

        $patrols = $query->orderBy('start_time', 'desc')->paginate(20);

        return $this->success($patrols, 'Patrols list.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'inspector_id' => 'required|exists:inspectors,id',
            'project_id'   => 'required|exists:projects,id',
            'guard_id'     => 'nullable|exists:guards,id',
            'start_time'   => 'required|date',
            'end_time'     => 'nullable|date|after_or_equal:start_time',
            'rating'       => 'nullable|integer|min:1|max:5',
            'notes'        => 'nullable|string',
        ]);

        $patrol = Patrol::create($data);
        $patrol->load(['inspector.user', 'project', 'securityGuard.user']);

        return $this->success($patrol, 'Patrol created successfully.', 201);
    }

    public function show(Patrol $patrol)
    {
        $patrol->load(['inspector.user', 'project', 'securityGuard.user']);

        return $this->success($patrol, 'Patrol details.');
    }

    public function destroy(Patrol $patrol)
    {
        $patrol->delete();

        return $this->success(null, 'Patrol deleted successfully.');
    }

        /**
     * 📌 دوريات الحارس الحالي (من خلال التوكن)
     * GET /api/guard/patrols
     * اختياري: ?date=2025-11-22
     */
    public function guardIndex(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->guard) {
            return response()->json([
                'message' => 'Only guards can access this endpoint.',
            ], 403);
        }

        $guard = $user->guard;

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
     * 🚶‍♂️ بدء دورية جديدة من الحارس
     * POST /api/guard/patrols
     */
    public function guardStore(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->guard) {
            return response()->json([
                'message' => 'Only guards can create patrols.',
            ], 403);
        }

        $guard = $user->guard;

        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'start_time' => 'nullable|date',      // لو ما انبعت → بنحط now()
            'notes'      => 'nullable|string',
        ]);

        $patrol = Patrol::create([
            'inspector_id' => null,                    // ممكن يتعبى من طرف المشرف لاحقاً
            'project_id'   => $data['project_id'],
            'guard_id'     => $guard->id,
            'start_time'   => $data['start_time'] ?? now(),
            'end_time'     => null,
            'rating'       => null,
            'notes'        => $data['notes'] ?? null,
        ]);

        $patrol->load(['project:id,name,location']);

        return response()->json([
            'message' => 'Patrol started successfully.',
            'data'    => $patrol,
        ], 201);
    }

    /**
     * 🏁 إنهاء دورية وتقييمها من طرف الحارس
     * POST /api/guard/patrols/{patrol}/end
     */
    public function guardEnd(Request $request, Patrol $patrol)
    {
        $user = $request->user();

        if (! $user || ! $user->guard) {
            return response()->json([
                'message' => 'Only guards can end patrols.',
            ], 403);
        }

        $guard = $user->guard;

        // نتأكد إن الدورية فعلاً لهذا الحارس
        if ($patrol->guard_id !== $guard->id) {
            return response()->json([
                'message' => 'This patrol does not belong to the current guard.',
            ], 403);
        }

        $data = $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'notes'  => 'nullable|string',
        ]);

        if ($patrol->end_time) {
            // خلصانة أصلاً
            return response()->json([
                'message' => 'Patrol already ended.',
                'data'    => $patrol,
            ]);
        }

        $patrol->end_time = now();

        if (array_key_exists('rating', $data)) {
            $patrol->rating = $data['rating'];
        }

        if (array_key_exists('notes', $data) && $data['notes'] !== null) {
            // نضيف ملاحظات جديدة أو نحدث القديمة
            $patrol->notes = $data['notes'];
        }

        $patrol->save();

        $patrol->load(['project:id,name,location']);

        return response()->json([
            'message' => 'Patrol ended successfully.',
            'data'    => $patrol,
        ]);
    }

}
