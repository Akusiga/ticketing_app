<x-layouts.admin title="Edit Lokasi">
    <div class="container mx-auto p-10">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-semibold mb-6">Edit Lokasi</h1>

            @if ($errors->any())
                <div class="alert alert-error mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card bg-base-100 shadow-md">
                <div class="card-body">
                    <form action="{{ route('admin.lokasi.update', $lokasi->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Nama Lokasi</span>
                            </label>
                            <input type="text" class="input input-bordered @error('nama_lokasi') input-error @enderror" placeholder="Masukkan nama lokasi" name="nama_lokasi" value="{{ old('nama_lokasi', $lokasi->nama_lokasi) }}" required>
                            @error('nama_lokasi')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="flex gap-2 justify-end">
                            <a href="{{ route('admin.lokasi.index') }}" class="btn btn-outline">Batal</a>
                            <button type="submit" class="btn btn-primary">Perbarui</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
