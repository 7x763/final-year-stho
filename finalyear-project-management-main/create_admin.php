<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating Super Admin user...\n";

// Ensure Role exists
try {
    if (!Role::where('name', 'super_admin')->exists()) {
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        echo "Role 'super_admin' created.\n";
    }
} catch (\Exception $e) {
    echo "Error checking/creating role: " . $e->getMessage() . "\n";
}

try {
    $user = User::firstOrCreate(
        ['email' => 'admin@admin.com'],
        [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]
    );

    if (!$user->wasRecentlyCreated) {
        $user->password = Hash::make('password');
        $user->save();
        echo "User 'admin@admin.com' updated with password 'password'.\n";
    } else {
        echo "User 'admin@admin.com' created with password 'password'.\n";
    }

    $user->assignRole('super_admin');
    echo "Role 'super_admin' assigned to user.\n";

} catch (\Exception $e) {
    echo "Error creating user: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Done.\n";
