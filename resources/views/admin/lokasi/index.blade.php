<x-layouts.admin title="Management Lokasi">
    <div class="container mx-auto p-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-semibold">Management Lokasi</h1>
            <a href="{{ route('admin.lokasi.create') }}" class="btn btn-primary">Tambah Lokasi Baru</a>
        </div>

        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th>ID</th>
                        <th>Nama Lokasi</th>
                        <th>Dibuat Pada</th>
                        <th>Diperbarui Pada</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lokasis as $lokasi)
                        <tr class="hover:bg-gray-50">
                            <td>{{ $lokasi->id }}</td>
                            <td>{{ $lokasi->nama_lokasi }}</td>
                            <td>{{ $lokasi->created_at->format('d-m-Y H:i') }}</td>
                            <td>{{ $lokasi->updated_at->format('d-m-Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.lokasi.edit', $lokasi->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.lokasi.destroy', $lokasi->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-error" onclick="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-gray-400">Tidak ada data lokasi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>
