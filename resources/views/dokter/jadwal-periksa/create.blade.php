<x-layouts.app title="Tambah Jadwal Periksa">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('jadwal-periksa.index') }}"
            class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-xs"></i>
        </a>
        <h2 class="text-xl font-bold text-slate-800">Tambah Jadwal Periksa</h2>
    </div>

    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-7">
            <form action="{{ route('jadwal-periksa.store') }}" method="POST">
                @csrf
                @include('dokter.jadwal-periksa._form', ['submitLabel' => 'Simpan Jadwal'])
            </form>
        </div>
    </div>
</x-layouts.app>
