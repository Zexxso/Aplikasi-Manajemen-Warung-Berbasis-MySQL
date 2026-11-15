<?php

require_once __DIR__ . '/../database/Migrator.php';

$action = $argv[1] ?? 'up';
$steps = isset($argv[2]) ? (int)$argv[2] : 1;

$migrator = new Migrator();

if ($action === 'down') {
    $migrator->down($steps);
} else {
    $migrator->up();
}
