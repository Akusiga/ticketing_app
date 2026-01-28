@props(['title', 'date', 'location', 'price', 'image', 'href' => null])

@php
// Format Indonesian price
$formattedPrice = $price ? 'Rp ' . number_format($price, 0, ',', '.') : 'Harga tidak tersedia';

$formattedDate = $date
? \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('d F Y, H:i')
: 'Tanggal tidak tersedia';

// Safe image URL: handle both storage and public/images paths
$imageUrl = $image
? (filter_var($image, FILTER_VALIDATE_URL)
? $image
: (str_contains($image, '/') 
    ? asset('images/' . $image)  // For images with path like 'events/filename.jpg'
    : asset('images/events/' . $image) // For images with just filename
  ))
: 'https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.webp';

// Get lokasi name from ID or object
$lokasiName = $location;
if (is_numeric($location)) {
    $lokasi = \App\Models\Lokasi::find($location);
    $lokasiName = $lokasi?->nama_lokasi ?? '-';
} elseif (is_object($location)) {
    $lokasiName = $location->nama_lokasi ?? '-';
}

@endphp

<a href="{{ $href ?? '#' }}" class="block">
    <div class="card bg-base-100 h-96 shadow-sm hover:shadow-md transition-shadow duration-300">
        <div class="h-48 overflow-hidden bg-gray-100 rounded-t-lg flex items-center justify-center">
            <img 
                src="{{ $imageUrl }}" 
                alt="{{ $title }}" 
                class="max-w-full max-h-full object-contain"
            >
        </div>

        <div class="card-body">
            <h2 class="card-title">
                {{ $title }}
            </h2>

            <p class="text-sm text-gray-500">
                {{ $formattedDate }}
            </p>

            <p class="text-sm">
                📍 {{ $lokasiName }}
            </p>

            <p class="font-bold text-lg mt-2">
                {{ $formattedPrice }}
            </p>

        </div>
    </div>
</a>