<x-layouts.app>
  <section class="max-w-7xl mx-auto py-12 px-6">
    <nav class="mb-6">
      <div class="breadcrumbs">
        <ul>
          <li><a href="{{ route('home') }}" class="link link-neutral">Beranda</a></li>
          <li><a href="#" class="link link-neutral">Event</a></li>
          <li>{{ $event->judul }}</li>
        </ul>
      </div>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left / Main area -->
      <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow">
          <figure>
            @php
              $imagePath = $event->gambar 
                ? (str_contains($event->gambar, '/') 
                    ? asset('images/' . $event->gambar) 
                    : asset('images/events/' . $event->gambar))
                : 'https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.webp';
            @endphp
            <img src="{{ $imagePath }}" alt="{{ $event->judul }}" class="w-full h-96 object-cover" onerror="this.src='https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.webp'" />
          </figure>
          <div class="card-body">
            <div class="flex justify-between items-start gap-4">
              <div>
                <h1 class="text-3xl font-extrabold">{{ $event->judul }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                  {{ \Carbon\Carbon::parse($event->tanggal_waktu)->locale('id')->translatedFormat('d F Y, H:i') }} • 📍
                  @if(is_object($event->lokasi))
                    {{ $event->lokasi->nama_lokasi }}
                  @else
                    {{ \App\Models\Lokasi::find($event->lokasi)?->nama_lokasi ?? '-' }}
                  @endif
                </p>

                <div class="mt-3 flex gap-2 items-center">
                  <span class="badge badge-primary">{{ $event->kategori?->nama ?? 'Tanpa Kategori' }}</span>
                  <span class="badge">{{ $event->user?->name ?? 'Penyelenggara' }}</span>
                </div>
              </div>

            </div>

            <p class="mt-4 text-gray-700 leading-relaxed">{{ $event->deskripsi }}</p>

            <div class="divider"></div>

            <h3 class="text-xl font-bold">Pilih Tiket</h3>

            <div class="mt-4 space-y-4">
              @forelse($event->tikets as $tiket)
              <div class="card card-side shadow-sm p-4 items-center">
                <div class="flex-1">
                  <h4 class="font-bold">{{ ucfirst($tiket->tipe) }}</h4>
                  <p class="text-sm text-gray-500">Stok: <span id="stock-{{ $tiket->id }}">{{ $tiket->stok }}</span></p>
                </div>

                <div class="w-44 text-right">
                  <div class="text-lg font-bold">
                    {{ $tiket->harga ? 'Rp ' . number_format($tiket->harga, 0, ',', '.') : 'Gratis' }}
                  </div>

                  <div class="mt-3 flex items-center justify-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline" data-action="dec" data-id="{{ $tiket->id }}"
                      aria-label="Kurangi satu">−</button>
                    <input id="qty-{{ $tiket->id }}" type="number" min="0" max="{{ $tiket->stok }}" value="0"
                      class="input input-bordered w-16 text-center" data-id="{{ $tiket->id }}" />
                    <button type="button" class="btn btn-sm btn-outline" data-action="inc" data-id="{{ $tiket->id }}"
                      aria-label="Tambah satu">+</button>
                  </div>

                  <div class="text-sm text-gray-500 mt-2">Subtotal: <span id="subtotal-{{ $tiket->id }}">Rp 0</span>
                  </div>
                </div>
              </div>
              @empty
              <div class="alert alert-info">Tiket belum tersedia untuk acara ini.</div>
              @endforelse
            </div>

          </div>
        </div>
      </div>

      <!-- Right / Summary -->
      <aside class="lg:col-span-1">
        <div class="card sticky top-24 p-4 bg-base-100 shadow">
          <h4 class="font-bold text-lg">Ringkasan Pembelian</h4>

          <div class="mt-4">
            <div class="flex justify-between text-sm text-gray-500"><span>Item</span><span id="summaryItems">0</span>
            </div>
            <div class="flex justify-between text-xl font-bold mt-1"><span>Total</span><span id="summaryTotal">Rp
                0</span></div>
          </div>

          <div class="divider"></div>

          <div id="selectedList" class="space-y-2 text-sm text-gray-700">
            <p class="text-gray-500">Belum ada tiket dipilih</p>
          </div>

          @auth
            <button id="checkoutButton" class="btn btn-primary !bg-blue-900 text-white btn-block mt-6" onclick="openCheckout()" disabled>Checkout</button>
          @else
            <a href="{{ route('login') }}" class="btn btn-primary btn-block mt-6 text-white">Login untuk Checkout</a>
          @endauth

        </div>
      </aside>
    </div>

    <!-- Checkout Modal -->
    <dialog id="checkout_modal" class="modal">
      <form method="dialog" class="modal-box">
        <h3 class="font-bold text-lg">Konfirmasi Pembelian</h3>
        <div class="mt-4 space-y-2 text-sm">
          <div id="modalItems">
            <p class="text-gray-500">Belum ada item.</p>
          </div>

          <div class="divider"></div>
          <div class="flex justify-between items-center">
            <span class="font-bold">Total</span>
            <span class="font-bold text-lg" id="modalTotal">Rp 0</span>
          </div>
        </div>

        <div class="modal-action">
          <button class="btn">Tutup</button>
          <button type="button" class="btn btn-primary px-4 !bg-blue-900 text-white" id="confirmCheckout">Konfirmasi</button>
        </div>
      </form>
    </dialog>

    <!-- Hidden data for JavaScript -->
    <script type="application/json" id="ticketsData">
    {
      @foreach($event->tikets as $tiket)
      "{{ $tiket->id }}": {
        "id": {{ $tiket->id }},
        "price": {{ $tiket->harga ?? 0 }},
        "stock": {{ $tiket->stok }},
        "tipe": "{{ e($tiket->tipeTicket->nama ?? '-') }}"
      }{{ !$loop->last ? ',' : '' }}
      @endforeach
    }
    </script>
    <input type="hidden" id="eventId" value="{{ $event->id }}">
    <input type="hidden" id="ordersStoreUrl" value="{{ route('orders.store') }}">
    <input type="hidden" id="ordersIndexUrl" value="{{ route('orders.index') }}">

  </section>

  @vite('resources/js/event-checkout.js')
</x-layouts.app>