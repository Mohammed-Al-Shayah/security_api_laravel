<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponseTrait;
use App\Models\Attendance;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    use ApiResponseTrait;

    /**
     * كم دقيقة بنسمح فيها كـ Late قبل ما نعتبره متأخر
     */
    protected int $lateToleranceMinutes = 10;

    /* =========================================================
     * 1) Admin Panel: Check-In باستخدام guard_id من البودي
     *    POST /api/attendance/check-in
     * ========================================================= */
    public function checkIn(Request $request)
    {
        $data = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'guard_id' => 'required|exists:guards,id',
            'lat'      => 'nullable|numeric',
            'lng'      => 'nullable|numeric',
        ]);

        $shift = Shift::findOrFail($data['shift_id']);

        // تأكيد إن الشيفت فعلاً لهذا الجارد
        if ($shift->guard_id !== (int) $data['guard_id']) {
            return $this->fail('Guard does not belong to this shift.', 422);
        }

        // نجيب أو ننشئ سجل الحضور
        $attendance = Attendance::firstOrCreate(
            [
                'shift_id' => $shift->id,
                'guard_id' => $data['guard_id'],
            ],
            []
        );

        // لو عامل تشيك إن قبل هيك
        if ($attendance->check_in_time) {
            $attendance->load('shift.project', 'guard.user');
            return $this->success($attendance, 'Already checked in.');
        }

        $now = now();

        $attendance->check_in_time = $now;
        $attendance->check_in_lat  = $data['lat'] ?? null;
        $attendance->check_in_lng  = $data['lng'] ?? null;

        // حساب التأخير
        $shiftStart = Carbon::parse($shift->date.' '.$shift->start_time);

        if ($now->greaterThan($shiftStart->copy()->addMinutes($this->lateToleranceMinutes))) {
            $attendance->status = 'LATE';
        } else {
            $attendance->status = 'ON_TIME';
        }

        $attendance->save();

        $attendance->load('shift.project', 'guard.user');

        return $this->success($attendance, 'Check-in recorded.');
    }

    /* =========================================================
     * 2) Admin Panel: Check-Out باستخدام guard_id من البودي
     *    POST /api/attendance/check-out
     * ========================================================= */
    public function checkOut(Request $request)
    {
        $data = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'guard_id' => 'required|exists:guards,id',
            'lat'      => 'nullable|numeric',
            'lng'      => 'nullable|numeric',
        ]);

        $attendance = Attendance::where('shift_id', $data['shift_id'])
            ->where('guard_id', $data['guard_id'])
            ->first();

        if (! $attendance) {
            return $this->fail('No check-in found for this shift.', 422);
        }

        if ($attendance->check_out_time) {
            $attendance->load('shift.project', 'guard.user');
            return $this->success($attendance, 'Already checked out.');
        }

        $shift = $attendance->shift;
        $now   = now();

        $attendance->check_out_time = $now;
        $attendance->check_out_lat  = $data['lat'] ?? null;
        $attendance->check_out_lng  = $data['lng'] ?? null;

        // لو طلع قبل نهاية الشفت
        $shiftEnd = Carbon::parse($shift->date.' '.$shift->end_time);
        if ($now->lessThan($shiftEnd)) {
            $attendance->status = 'LEFT_EARLY';
        }

        $attendance->save();

        $attendance->load('shift.project', 'guard.user');

        return $this->success($attendance, 'Check-out recorded.');
    }

    /* =========================================================
     * 3) Guard Mobile: Check-In من خلال التوكن
     *    POST /api/guard/attendance/check-in
     *    البودي: { "shift_id": 5, "lat": .., "lng": .. }
     * ========================================================= */
    public function guardCheckIn(Request $request)
    {
       $user = $request->user();

    if (! $user) {
        // ما في يوزر من التوكن
        return $this->fail([
            'reason' => 'no_user_from_token',
        ], 401);
    }

    if (! $user->guard) {
        // اليوزر موجود، بس ما إله Guard مربوط
        return $this->fail([
            'reason'     => 'user_has_no_guard_relation',
            'user_id'    => $user->id,
            'user_email' => $user->email,
        ], 403);
    }

    $guardId = $user->guard->id;

        $data = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'lat'      => 'nullable|numeric',
            'lng'      => 'nullable|numeric',
        ]);

        $shift = Shift::where('id', $data['shift_id'])
            ->where('guard_id', $guardId)
            ->first();

        if (! $shift) {
            return $this->fail('Guard does not belong to this shift.', 422);
        }

        $attendance = Attendance::firstOrCreate(
            [
                'shift_id' => $shift->id,
                'guard_id' => $guardId,
            ],
            []
        );

        if ($attendance->check_in_time) {
            $attendance->load('shift.project', 'guard.user');
            return $this->success($attendance, 'Already checked in.');
        }

        $now = now();

        $attendance->check_in_time = $now;
        $attendance->check_in_lat  = $data['lat'] ?? null;
        $attendance->check_in_lng  = $data['lng'] ?? null;

        $shiftStart = Carbon::parse($shift->date.' '.$shift->start_time);

        if ($now->greaterThan($shiftStart->copy()->addMinutes($this->lateToleranceMinutes))) {
            $attendance->status = 'LATE';
        } else {
            $attendance->status = 'ON_TIME';
        }

        $attendance->save();

        $attendance->load('shift.project', 'guard.user');

        return $this->success($attendance, 'Check-in recorded.');
    }

    /* =========================================================
     * 4) Guard Mobile: Check-Out من خلال التوكن
     *    POST /api/guard/attendance/check-out
     *    البودي: { "shift_id": 5, "lat": .., "lng": .. }
     * ========================================================= */
    public function guardCheckOut(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->guard) {
            return $this->fail('Only guards can perform check-out.', 403);
        }

        $guardId = $user->guard->id;

        $data = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'lat'      => 'nullable|numeric',
            'lng'      => 'nullable|numeric',
        ]);

        $attendance = Attendance::where('shift_id', $data['shift_id'])
            ->where('guard_id', $guardId)
            ->first();

        if (! $attendance) {
            return $this->fail('No check-in found for this shift.', 422);
        }

        if ($attendance->check_out_time) {
            $attendance->load('shift.project', 'guard.user');
            return $this->success($attendance, 'Already checked out.');
        }

        $shift = $attendance->shift;
        $now   = now();

        $attendance->check_out_time = $now;
        $attendance->check_out_lat  = $data['lat'] ?? null;
        $attendance->check_out_lng  = $data['lng'] ?? null;

        $shiftEnd = Carbon::parse($shift->date.' '.$shift->end_time);
        if ($now->lessThan($shiftEnd)) {
            $attendance->status = 'LEFT_EARLY';
        }

        $attendance->save();

        $attendance->load('shift.project', 'guard.user');

        return $this->success($attendance, 'Check-out recorded.');
    }
}
