<x-layouts.app title="Data Pasien">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Data Pasien
        </h2>

        <a href="{{ route('pasien.create') }}" class="btn bg-[#2d4499] hover:bg-[#1e2d6b]
                  text-white border-none rounded-lg px-5">
            <i class="fas fa-plus"></i>
            Tambah Pasien
        </a>
    </div>

    {{-- Alert Success --}}
    @if(session('message'))
    <div class="alert alert-{{ session('type', 'success') }} mb-4 rounded-xl shadow-sm">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('message') }}</span>
    </div>
    @endif

    {{-- Card --}}
    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">

                    {{-- Head --}}
                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">No. RM</th>
                            <th class="px-6 py-4">Nama</th>
                            <th class="px-6 py-4">No. HP</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    {{-- Body --}}
                    <tbody>
                        @forelse($pasiens as $pasien)
                        <tr class="hover:bg-slate-50 transition">

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $pasien->no_rm ?? '-' }}
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $pasien->nama }}
                            </td>

                            <td class="px-6 py-4 text-slate-500">
                                {{ $pasien->no_hp }}
                            </td>

                            <td class="px-6 py-4 text-slate-500">
                                {{ $pasien->email }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">

                                    {{-- Edit --}}
                                    <a href="{{ route('pasien.edit', $pasien->id) }}" class="btn btn-sm bg-amber-500 hover:bg-amber-600
                                                  text-white border-none rounded-lg px-4">
                                        <i class="fas fa-pen-to-square"></i>
                                        Edit
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('pasien.destroy', $pasien->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            onclick="return confirm('Yakin ingin menghapus pasien ini?')" class="btn btn-sm bg-red-500 hover:bg-red-600
                                                       text-white border-none rounded-lg px-4">
                                            <i class="fas fa-trash"></i>
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-14 text-slate-400">
                                <i class="fas fa-inbox text-3xl mb-3 block"></i>
                                Belum ada data pasien
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</x-layouts.app>
