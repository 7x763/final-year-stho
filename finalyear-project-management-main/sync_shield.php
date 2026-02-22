<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
$role->syncPermissions(Permission::all());
echo "Synced " . Permission::count() . " permissions to super_admin role.\n";
