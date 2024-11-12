<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

    // إنشاء 10 مستخدمين وربطهم بالدور "admin"
    User::factory()->count(10)->create()->each(function ($user) use ($adminRole) {
        $user->roles()->attach($adminRole->id);
    });
    }
}
