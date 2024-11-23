<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    //login
    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $loginData['email'])->first();

        //check user exist
        if (!$user) {
            return response(['message' => 'Invalid credentials'], 401);
        }

        //check password
        if (!Hash::check($loginData['password'], $user->password)) {
            return response(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response(['user' => $user, 'token' => $token], 200);
    }

    // logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response(['message' => 'Logged out'], 200);
    }

    // update image profile & face_embedding
    // public function updateProfile(Request $request)
    // {
    //     $request->validate([
    //         'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //         'face_embedding' => 'required',
    //     ]);

    //     $user = $request->user();
    //     $image = $request->file('image');
    //     $face_embedding = $request->face_embedding;

    //     // save image
    //     $image->storeAs('images', $image->hashName(), 'public');
    //     $user->img_url = $image->hashName();
    //     $user->face_embedding = $face_embedding;

    //     $user->save();

    //     return response(['message' => 'Profile updated', 'user' => $user], 200);
    // }
    public function updateProfile(Request $request)
    {
        // Validasi input
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'face_embedding' => 'required',
        ]);

        // Dapatkan user dari request
        $user = $request->user();

        // Proses upload gambar
        if ($request->hasFile('image')) {
            // Simpan gambar di disk 'public'
            $image = $request->file('image');
            $path = $image->storeAs('images', $image->hashName(), 'public');

            // Hapus gambar lama jika ada
            if ($user->img_url) {
                // Extract the file name from the existing URL and delete from storage
                $oldImagePath = str_replace(url('storage/'), '', $user->img_url);
                Storage::disk('public')->delete($oldImagePath);
            }

            // Simpan URL gambar ke database
            $user->img_url = url('storage/images/' . $image->hashName());
        }

        // Simpan face embedding ke database
        $user->face_embedding = $request->face_embedding;

        // Simpan perubahan ke database
        $user->save();

        return response([
            'message' => 'Profile updated',
            'user' => $user
        ], 200);
    }
}
