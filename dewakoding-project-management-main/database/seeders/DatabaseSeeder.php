<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tạo tài khoản Admin mặc định
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Chạy RoleSeeder để tạo quyền và vai trò
        $this->call(RoleSeeder::class);

        // Gán quyền super_admin cho các tài khoản admin
        $admins = User::whereIn('email', ['admin@admin.com', 'test@example.com'])->get();
        foreach ($admins as $admin) {
            $admin->assignRole('super_admin');
        }

        // Chạy DemoSeeder để tạo dữ liệu mẫu
        $this->call(DemoSeeder::class);
    }
}
