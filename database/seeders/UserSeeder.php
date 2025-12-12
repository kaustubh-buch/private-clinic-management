<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@tatvasoft.com'],
            [
                'first_name'        => 'Admin',
                'password'          => 'Test@123',
                'email_verified_at' => Carbon::now(),
            ]
        );
        $user->assignRole('admin');
    }
}
