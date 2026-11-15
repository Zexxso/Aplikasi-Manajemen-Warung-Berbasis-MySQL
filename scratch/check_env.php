<?php
$env = file_get_contents(__DIR__ . '/../../warungku/.env');
preg_match('/SESSION_DRIVER=(.*)/', $env, $mSession);
preg_match('/CACHE_STORE=(.*)/', $env, $mCache);
preg_match('/QUEUE_CONNECTION=(.*)/', $env, $mQueue);

echo "SESSION_DRIVER: " . ($mSession[1] ?? 'not set') . "\n";
echo "CACHE_STORE: " . ($mCache[1] ?? 'not set') . "\n";
echo "QUEUE_CONNECTION: " . ($mQueue[1] ?? 'not set') . "\n";
