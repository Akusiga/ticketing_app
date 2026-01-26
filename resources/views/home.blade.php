<x-layouts.app>
    <div class="hero bg-blue-900 min-h-screen">
        <div class="hero-content text-center text-white">
            <div class="max-w-4xl">
                <h1 class="text-5xl font-bold">Hi, Amankan Tiketmu yuk.</h1>
                <p class="py-6">
                    BengTix: Beli tiket, auto asik.
                </p>
            </div>
        </div>
    </div>

    <section class="max-w-7xl mx-auto py-12 px-6">
        <h2 class="text-2xl font-black uppercase italic mb-8">Event</h2>
        
       @php
    $activeCategory = $categories->firstWhere('id', request('kategori'));
@endphp

<div class="mb-8 flex gap-2 items-center">
    <!-- Dropdown Kategori (Dynamic Label) -->
    <div class="dropdown">
        <button tabindex="0" class="btn btn-primary">
            {{ $activeCategory ? $activeCategory->nama : 'Semua' }} ▼
        </button>

        <ul tabindex="0"
            class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52 max-h-64 overflow-y-auto">
            
            <!-- Semua -->
            <li>
                <a href="{{ route('home') }}"
                   class="{{ !request('kategori') ? 'active' : '' }}">
                    Semua
                </a>
            </li>

            @foreach($categories as $kategori)
                <li>
                    <a href="{{ route('home', ['kategori' => $kategori->id]) }}"
                       class="{{ request('kategori') == $kategori->id ? 'active' : '' }}">
                        {{ $kategori->nama }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>


        <!-- Event Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($events as $event)
            <x-user.event-card :title="$event->judul" :date="$event->tanggal_waktu" :location="$event->lokasi"
                :price="$event->tikets_min_harga" :image="$event->gambar" :href="route('events.show', $event)" />
            @endforeach
        </div>

    </section>
</x-layouts.app>