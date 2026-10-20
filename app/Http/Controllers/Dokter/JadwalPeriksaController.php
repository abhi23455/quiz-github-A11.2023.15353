<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalPeriksaController extends Controller
{
    private const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    public function index()
    {
        $jadwals = JadwalPeriksa::where('id_dokter', Auth::id())
            ->orderByRaw("CASE hari
                WHEN 'Senin' THEN 1
                WHEN 'Selasa' THEN 2
                WHEN 'Rabu' THEN 3
                WHEN 'Kamis' THEN 4
                WHEN 'Jumat' THEN 5
                WHEN 'Sabtu' THEN 6
                WHEN 'Minggu' THEN 7
            END")
            ->orderBy('jam_mulai')
            ->get();

        return view('dokter.jadwal-periksa.index', compact('jadwals'));
    }

    public function create()
    {
        return view('dokter.jadwal-periksa.create', ['hariList' => self::HARI]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $validated['id_dokter'] = Auth::id();

        JadwalPeriksa::create($validated);

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('success', 'Jadwal periksa berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $jadwal = $this->jadwalMilikDokter($id);

        return view('dokter.jadwal-periksa.edit', [
            'jadwal' => $jadwal,
            'hariList' => self::HARI,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate($this->rules());
        $jadwal = $this->jadwalMilikDokter($id);
        $jadwal->update($validated);

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('success', 'Jadwal periksa berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $this->jadwalMilikDokter($id)->delete();

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('success', 'Jadwal periksa berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'hari' => ['required', 'in:' . implode(',', self::HARI)],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ];
    }

    private function jadwalMilikDokter(string $id): JadwalPeriksa
    {
        return JadwalPeriksa::where('id_dokter', Auth::id())->findOrFail($id);
    }
}
