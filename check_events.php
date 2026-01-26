<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$events = \App\Models\Event::select('id', 'judul', 'gambar')->get();
foreach ($events as $e) {
    echo 'ID: ' . $e->id . ' | Judul: ' . $e->judul . ' | Gambar: ' . ($e->gambar ?? 'NULL') . PHP_EOL;
}
