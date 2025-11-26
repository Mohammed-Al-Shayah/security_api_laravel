<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponseTrait;
use App\Models\Guard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GuardController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $guards = Guard::with('user')->paginate(15);

        return $this->success($guards, 'Guards list.');
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'         => 'required|string|max:255',
                'email'        => 'required|email|unique:users,email',
                'phone'        => 'required|string|max:20',
                'password'     => 'required|string|min:6',
                'national_id'  => 'nullable|string|max:50',
                'badge_number' => 'nullable|string|max:50',
                'status'       => 'nullable|in:ACTIVE,INACTIVE',
            ]);

            // ✅ أنشئ الـ User فقط بالأعمدة المضمونة الموجودة في جدول users في Render
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                // لو لاحقًا ضفت أعمدة مثل role / phone / is_active بمigration جديد
                // ترجع تضيفها هنا بعد ما تشغّل migrate على Render.
            ]);

            // ✅ أنشئ Guard مربوط بالـ user
            $guard = Guard::create([
                'user_id'      => $user->id,
                'national_id'  => $data['national_id'] ?? null,
                'badge_number' => $data['badge_number'] ?? null,
                'status'       => $data['status'] ?? 'ACTIVE',
                // لو جدول guards فيه phone حابب تخزّنه هناك:
                // 'phone' => $data['phone'],
            ]);

            $guard->load('user');

            return $this->success($guard, 'Guard created successfully.', 201);

        } catch (\Throwable $e) {
            // 🔥 مؤقتًا نرجّع الرسالة عشان لو ظل في مشكلة نعرفها بسرعة
            return response()->json([
                'message' => 'DEBUG ERROR (guards.store)',
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ], 500);
        }
    }

    public function show(Guard $guard)
    {
        $guard->load('user', 'projects');

        return $this->success($guard, 'Guard details.');
    }

    public function update(Request $request, Guard $guard)
    {
        $data = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'email'        => 'sometimes|email|unique:users,email,' . $guard->user_id,
            'phone'        => 'sometimes|string|max:20',
            'password'     => 'nullable|string|min:6',
            'national_id'  => 'nullable|string|max:50',
            'badge_number' => 'nullable|string|max:50',
            'status'       => 'sometimes|in:ACTIVE,INACTIVE',
        ]);

        $user = $guard->user;

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }
        if (isset($data['email'])) {
            $user->email = $data['email'];
        }
        if (isset($data['phone'])) {
            // لو جدول users ما فيه phone، احذف هذا السطر
            $user->phone = $data['phone'];
        }
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        if (isset($data['national_id'])) {
            $guard->national_id = $data['national_id'];
        }
        if (isset($data['badge_number'])) {
            $guard->badge_number = $data['badge_number'];
        }
        if (isset($data['status'])) {
            $guard->status = $data['status'];
        }
        $guard->save();

        $guard->load('user');

        return $this->success($guard, 'Guard updated successfully.');
    }

    public function destroy(Guard $guard)
    {
        $user = $guard->user;

        // لو users ما فيه is_active، إمّا:
        // 1) تحذف العمود من هنا، أو
        // 2) تضيف العمود بمigration وتشغّل migrate في Render
        if (property_exists($user, 'is_active') || array_key_exists('is_active', $user->getAttributes())) {
            $user->is_active = false;
            $user->save();
        }

        return $this->success(null, 'Guard deactivated.');
    }
}
