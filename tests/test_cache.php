<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\cache\FileCache;

$cache = new FileCache(__DIR__ ."../cache");

// stcoker une donnée
$cache->set('user_123', ['name' => 'alice', 'role' => 'admin'], 600);

$user = $cache->get('user_123');
