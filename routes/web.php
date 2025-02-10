<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

// Route Default: Menampilkan Halaman Login
Route::get('/', function () {
    return view('pages.auth.auth-login');
});

// Middleware 'auth' digunakan untuk semua route dalam grup ini
Route::middleware(['auth'])->group(function () {

    // Dashboard (Home)
    Route::get('home', function () {
        $tanggal = Carbon::now();

        // Hitung total karyawan & kehadiran hari ini
        $totalKaryawan = User::where('role', 'karyawan')->count();
        $totalHadir = Attendance::whereDate('created_at', $tanggal)->count();

        // Ambil daftar karyawan yang hadir hari ini
        $hadirHariIni = Attendance::whereDate('created_at', $tanggal)->with('user')->get();
        $users = User::with('permissions')->get();

        // Data untuk grafik kehadiran bulanan
        $attendanceData = [];
        $absentData = [];
        $attendanceLabels = [];
        $absentDetails = [];
        $presentDetails = [];

        $daysInMonth = Carbon::now()->daysInMonth;
        $totalKaryawan = User::count();

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::now()->startOfMonth()->addDays($i - 1);

            // Ambil ID & nama karyawan yang hadir
            $hadir = Attendance::whereDate('created_at', $date)->with('user')->get();

            // Ambil ID & nama karyawan yang absen
            $absen = User::whereNotIn('id', $hadir->pluck('user_id'))->get();

            $attendanceLabels[] = $date->format('d M');
            $attendanceData[] = $hadir->count();
            $absentData[] = $totalKaryawan - $hadir->count();

            // Simpan daftar nama karyawan yang hadir & absen
            $presentDetails[$date->format('d M')] = $hadir->pluck('user.name')->toArray();
            $absentDetails[$date->format('d M')] = $absen->pluck('name')->toArray();
        }

        return view('pages.dashboard', compact(
            'totalKaryawan',
            'tanggal',
            'totalHadir',
            'attendanceLabels',
            'attendanceData',
            'absentData',
            'presentDetails',
            'absentDetails',
            'hadirHariIni',
            'users'
        ));
    })->name('home');

    // CRUD untuk User, Perusahaan, Kehadiran, dan Izin
    Route::resource('user', UserController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('attendances', AttendanceController::class);
    Route::resource('permissions', PermissionController::class);

    // ========================
    // ✅ ROUTE UNTUK DATA IZIN
    // ========================

    // Menampilkan halaman pilih karyawan dan daftar izinnya
    Route::get('/izin-karyawan', function () {
        $users = User::all(); // Ambil semua user
        return view('izin_karyawan', compact('users'));
    });

    // API untuk mendapatkan data izin berdasarkan karyawan yang dipilih
    Route::get('/get-permissions/{id}', function ($id) {
        $user = User::with('permissions')->find($id);

        if ($user) {
            return response()->json($user->permissions);
        } else {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }
    });
});
