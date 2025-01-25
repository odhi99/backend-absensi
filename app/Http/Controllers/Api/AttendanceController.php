<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    //checkin
    public function checkin(Request $request)
    {
        // validate lat and long
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        // save absen baru
        $attendance = new Attendance;
        $attendance->user_id = $request->user()->id;
        $attendance->date = date('Y-m-d');
        $attendance->time_in = Carbon::now()->timezone('Asia/Jakarta')->format('H:i:s'); // Menggunakan Carbon
        $attendance->latlon_in = $request->latitude . ',' . $request->longitude;
        $attendance->save();

        return response([
            'message' => 'Checkin success',
            'attendance' => $attendance
        ], 200);
    }

    //checkout
    public function checkout(Request $request)
    {
        //validate lat and long
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        //get today attendance
        $attendance = Attendance::where('user_id', $request->user()->id)
            ->where('date', date('Y-m-d'))
            ->first();

        //check if attendance not found
        if (!$attendance) {
            return response(['message' => 'Checkin first'], 400);
        }

        //save checkout
        $attendance->time_out = date('H:i:s');
        $attendance->latlon_out = $request->latitude . ',' . $request->longitude;
        $attendance->save();

        return response([
            'message' => 'Checkout success',
            'attendance' => $attendance
        ], 200);
    }

    // check is checkedin
    public function isCheckedin(Request $request)
    {
        // get today attendance
        $attendence = Attendance::where('user_id', $request->user()->id)
            ->where('date', date('Y-m-d'))->first();

        $isCheckout = $attendence ? $attendence->time_out : false;

        return response([
            'checkedin' => $attendence ? true : false,
            'checkedout' => $isCheckout ? true : false,
        ], 200);
    }
}
