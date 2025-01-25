<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;


class PermissionController extends Controller
{
    //index
    public function index(Request $request)
    {
        $permissions = Permission::with('user')->when($request->input('name'), function ($query, $name) {
            $query->whereHas('user', function ($query) use ($name) {
                $query->where('name', 'like', '%' . $name . '%');
            });
        })->latest()->paginate(10);
        return view('pages.permission.index', compact('permissions'));
    }

    // show
    public function show($id)
    {
        $permission = Permission::with('user')->find($id);
        return view('pages.permission.show', compact('permission'));
    }

    // edit
    public function edit($id)
    {
        $permission = Permission::find($id);
        return view('pages.permission.edit', compact('permission'));
    }

    // update
    public function update(Request $request, $id)
    {
        $permission = Permission::find($id);
        $permission->is_approved = $request->is_approved;
        $str = $request->is_approved == 1 ? 'Disetujui' : 'Ditolak';
        $permission->save();

        // Kirim notifikasi hanya jika pengguna memiliki token
        $this->sendNotificationToUser($permission->user_id, 'Status Izin anda adalah ' . $str);

        return redirect()->route('permissions.index')->with('success', 'Permission telah di update');
    }

    public function sendNotificationToUser($userId, $message)
    {
        // Dapatkan user dan FCM token
        $user = User::find($userId);

        if (!$user || !$user->fcm_token) {
            // Jika token tidak ada, beri tahu pengguna dengan cara lain
            return redirect()->route('permissions.index')->with('error', "User dengan ID {$userId} tidak memiliki FCM token.");
        }

        $token = $user->fcm_token;

        // Kirim notifikasi ke perangkat Android
        $messaging = app('firebase.messaging');
        $notification = Notification::create('Status Izin', $message);

        try {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification);

            $messaging->send($message);
        } catch (\Exception $e) {
            // Tangani error tanpa menggunakan log
            return redirect()->route('permissions.index')->with('error', "Gagal mengirim notifikasi: " . $e->getMessage());
        }
    }
}
