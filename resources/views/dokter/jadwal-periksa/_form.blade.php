<div class="form-control mb-5">
    <label for="hari" class="label pb-1">
        <span class="text-sm font-semibold text-gray-700">Hari <span class="text-red-500">*</span></span>
    </label>
    <select id="hari" name="hari"
        class="select select-bordered w-full rounded-lg text-sm @error('hari') select-error @enderror" required>
        <option value="">Pilih hari</option>
        @foreach($hariList as $hari)
        <option value="{{ $hari }}" {{ old('hari', $jadwal->hari ?? '') === $hari ? 'selected' : '' }}>
            {{ $hari }}
        </option>
        @endforeach
    </select>
    @error('hari')
    <label class="label pt-1"><span class="label-text-alt text-red-500">{{ $message }}</span></label>
    @enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
    <div class="form-control">
        <label for="jam_mulai" class="label pb-1">
            <span class="text-sm font-semibold text-gray-700">Jam Mulai <span class="text-red-500">*</span></span>
        </label>
        <input id="jam_mulai" type="time" name="jam_mulai"
            value="{{ old('jam_mulai', isset($jadwal) ? substr($jadwal->jam_mulai, 0, 5) : '') }}"
            class="input input-bordered w-full rounded-lg text-sm @error('jam_mulai') input-error @enderror" required>
        @error('jam_mulai')
        <label class="label pt-1"><span class="label-text-alt text-red-500">{{ $message }}</span></label>
        @enderror
    </div>

    <div class="form-control">
        <label for="jam_selesai" class="label pb-1">
            <span class="text-sm font-semibold text-gray-700">Jam Selesai <span class="text-red-500">*</span></span>
        </label>
        <input id="jam_selesai" type="time" name="jam_selesai"
            value="{{ old('jam_selesai', isset($jadwal) ? substr($jadwal->jam_selesai, 0, 5) : '') }}"
            class="input input-bordered w-full rounded-lg text-sm @error('jam_selesai') input-error @enderror" required>
        @error('jam_selesai')
        <label class="label pt-1"><span class="label-text-alt text-red-500">{{ $message }}</span></label>
        @enderror
    </div>
</div>

<div class="flex gap-3">
    <button type="submit"
        class="flex items-center gap-2 px-6 py-2.5 bg-[#2d4499] hover:bg-[#1e2d6b] text-white rounded-lg text-sm font-semibold transition">
        <i class="fas fa-save"></i>
        {{ $submitLabel }}
    </button>
    <a href="{{ route('jadwal-periksa.index') }}"
        class="flex items-center gap-2 px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-lg text-sm font-semibold transition">
        Batal
    </a>
</div>
