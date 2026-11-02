<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PoliController extends Controller
{
    public function get()
    {
        $user = Auth::user();
        if (blank($user->no_rm)) {
            $user->no_rm = User::generateNoRm();
            $user->save();
        }

        $polis = Poli::all();
        $jadwal = JadwalPeriksa::with('dokter', 'dokter.poli')
            ->where('status', true)
            ->get();

        return view('pasien.daftar', [
            'user' => $user,
            'polis' => $polis,
            'jadwals' => $jadwal,
        ]);
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'id_poli' => 'required|exists:poli,id',
            'id_jadwal' => 'required|exists:jadwal_periksa,id',
            'keluhan' => 'nullable|string',
        ]);

        $jadwal = JadwalPeriksa::whereKey($validated['id_jadwal'])
            ->where('status', true)
            ->whereHas('dokter', function ($query) use ($validated) {
                $query->where('id_poli', $validated['id_poli']);
            })
            ->first();

        if (!$jadwal) {
            return back()
                ->withErrors(['id_jadwal' => 'Jadwal tidak tersedia untuk poli yang dipilih.'])
                ->withInput();
        }

        $jumlahSudahDaftar = DaftarPoli::where('id_jadwal', $jadwal->id)->count();

        DaftarPoli::create([
            'id_pasien' => Auth::id(),
            'id_jadwal' => $jadwal->id,
            'keluhan' => $validated['keluhan'] ?? '',
            'no_antrian' => $jumlahSudahDaftar + 1,
        ]);

        return redirect()
            ->back()
            ->with('message', 'Berhasil Mendaftar ke Poli')
            ->with('type', 'success');
    }
}