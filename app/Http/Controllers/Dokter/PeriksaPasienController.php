<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeriksaPasienController extends Controller
{
    public function index()
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksas'])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    public function create($id)
    {
        $daftarPoli = $this->daftarPoliMilikDokter($id);
        abort_if($daftarPoli->periksas()->exists(), 404);

        $obats = Obat::all();
        return view('dokter.periksa-pasien.create', [
            'obats' => $obats,
            'id' => $daftarPoli->id,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_daftar_poli' => ['required', 'integer', 'exists:daftar_poli,id'],
            'obat_json' => ['required', 'json'],
            'catatan' => 'nullable|string',
        ]);

        $daftarPoli = $this->daftarPoliMilikDokter($validated['id_daftar_poli']);
        if ($daftarPoli->periksas()->exists()) {
            return back()->withErrors(['id_daftar_poli' => 'Pasien ini sudah diperiksa.']);
        }

        $obatIds = json_decode($validated['obat_json'], true);
        if (!is_array($obatIds) || !array_is_list($obatIds) || array_filter($obatIds, fn ($id) => !is_int($id) && !(is_string($id) && ctype_digit($id)))) {
            return back()->withErrors(['obat_json' => 'Daftar obat tidak valid.']);
        }

        $obatIds = array_values(array_unique(array_map('intval', $obatIds)));
        $obats = Obat::whereIn('id', $obatIds)->get();
        if ($obats->count() !== count($obatIds)) {
            return back()->withErrors(['obat_json' => 'Satu atau lebih obat tidak ditemukan.']);
        }

        DB::transaction(function () use ($validated, $daftarPoli, $obatIds, $obats) {
            $periksa = Periksa::create([
                'id_daftar_poli' => $daftarPoli->id,
                'tgl_periksa' => now(),
                'catatan' => $validated['catatan'] ?? null,
                'biaya_periksa' => $obats->sum('harga') + 150000,
            ]);

            foreach ($obatIds as $idObat) {
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat' => $idObat,
                ]);
            }
        });

        return redirect()
            ->route('periksa-pasien.index')
            ->with('success', 'Data periksa berhasil disimpan.');
    }

    private function daftarPoliMilikDokter(int|string $id): DaftarPoli
    {
        return DaftarPoli::whereKey($id)
            ->whereHas('jadwalPeriksa', function ($query) {
                $query->where('id_dokter', Auth::id());
            })
            ->firstOrFail();
    }
}