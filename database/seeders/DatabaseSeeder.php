<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        User::factory()->create([
            'name' => 'Odi Admin',
            'email' => 'odhii9671@gmail.com',
            'password' => Hash::make('odi123'),
        ]);

        // data dummy untuk company
        \App\Models\Company::create([
            'name' => 'PT. Rantemalio',
            'email' => 'rantemalio@gmail.com',
            'address' => 'Kompleks Bouvardia Jl.Bouvardia Cros No.8 Barombong, Kec. Tamalate, Kota Makassar, Sulawesi Selatan 90225',
            'latitude' => '-5.140286943212265',
            'longitude' => '119.4830788231449',
            'radius_km' => '0.5',
            'time_in' => '08:00',
            'time_out' => '17:00',
        ]);

        $this->call([
            AttendanceSeeder::class,
            PermissionSeeder::class,
        ]);
    }
}
