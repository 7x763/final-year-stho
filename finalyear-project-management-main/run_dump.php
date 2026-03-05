<?php
$dumpPath = 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe';
$command = "\"$dumpPath\" -u root -proot project_management > fresh_local_dump.sql";
exec($command, $output, $returnVar);

if ($returnVar === 0) {
    echo "Dump created successfully.\n";
} else {
    echo "Dump failed with error code $returnVar.\n";
    print_r($output);
}
