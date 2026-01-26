<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Event;

$event = Event::where('judul', 'aimer')->first();
if ($event) {
    echo "Event ID: " . $event->id . PHP_EOL;
    echo "Judul: " . $event->judul . PHP_EOL;
    echo "Gambar di DB: " . ($event->gambar ?? 'NULL') . PHP_EOL;
    echo "Path lengkap: " . asset($event->gambar) . PHP_EOL;
} else {
    echo "Event tidak ditemukan\n";
}
