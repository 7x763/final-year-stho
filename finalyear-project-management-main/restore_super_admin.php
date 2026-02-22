<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Restoring permissions for super_admin role...\n";

try {
    $role = Role::where('name', 'super_admin')->first();
    if (!$role) {
        die("Error: super_admin role not found. Run create_admin.php first.\n");
    }

    $permissions = Permission::all();
    $role->syncPermissions($permissions);
    
    echo "Successfully synced " . $permissions->count() . " permissions to super_admin.\n";
    
    // Also ensure the admin user exists and has the role
    $user = User::where('email', 'admin@admin.com')->first();
    if ($user) {
        $user->assignRole($role);
        echo "Role assigned to admin@admin.com.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Done.\n";
