<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$db = config('database.default');
$schema = config("database.connections.$db.database");

echo "Checking Database: $schema\n";

$dbResult = DB::select("SELECT default_character_set_name, default_collation_name FROM information_schema.schemata WHERE schema_name = ?", [$schema]);
print_r($dbResult);

echo "\nChecking Table: tickets\n";
$tableResult = DB::select("SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'tickets'", [$schema]);
print_r($tableResult);

echo "\nChecking Column Collation for 'tickets':\n";
$columnResult = DB::select("SELECT COLUMN_NAME, COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'tickets'", [$schema]);
print_r($columnResult);
