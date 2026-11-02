<?php

namespace Tests\Feature;

use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoliklinikFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_view_and_register_for_a_matching_poli_schedule(): void
    {
        $this->withoutVite();

        [$pasien, $dokter, $jadwal] = $this->buatKlinik();

        $this->actingAs($pasien)
            ->get(route('pasien.daftar'))
            ->assertOk()
            ->assertSee('Pendaftaran Poli');

        $this->actingAs($pasien)
            ->post(route('pasien.daftar.submit'), [
                'id_pasien' => $dokter->id,
                'id_poli' => $dokter->id_poli,
                'id_jadwal' => $jadwal->id,
                'keluhan' => 'Demam',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('daftar_poli', [
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'keluhan' => 'Demam',
            'no_antrian' => 1,
        ]);
    }

    public function test_patient_record_number_is_generated_and_backfilled_for_existing_patients(): void
    {
        $this->withoutVite();

        $pasien = User::create([
            'nama' => 'Pasien',
            'email' => 'nomor-rm@example.com',
            'password' => 'password',
            'role' => 'pasien',
        ]);
        $this->assertMatchesRegularExpression('/^\d{6}-\d{3,}$/', $pasien->no_rm);

        $pasienLama = User::create([
            'nama' => 'Pasien Lama',
            'email' => 'pasien-lama@example.com',
            'password' => 'password',
            'role' => 'pasien',
        ]);
        User::whereKey($pasienLama->id)->update(['no_rm' => null]);

        $this->actingAs($pasienLama)
            ->get(route('pasien.daftar'))
            ->assertOk()
            ->assertSee(User::findOrFail($pasienLama->id)->no_rm);

        $this->assertDatabaseHas('users', [
            'id' => $pasienLama->id,
            'no_rm' => User::findOrFail($pasienLama->id)->no_rm,
        ]);
    }

    public function test_doctor_can_examine_and_view_patient_history(): void
    {
        $this->withoutVite();

        [, $dokter, $jadwal] = $this->buatKlinik();
        $pasien = User::create([
            'nama' => 'Pasien',
            'email' => 'pasien@example.com',
            'password' => 'password',
            'role' => 'pasien',
        ]);
        $daftarPoli = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'keluhan' => 'Demam',
            'no_antrian' => 1,
        ]);
        $obat = Obat::create([
            'nama_obat' => 'Paracetamol',
            'kemasan' => 'Tablet',
            'harga' => 5000,
        ]);

        $this->actingAs($dokter)
            ->get(route('periksa-pasien.index'))
            ->assertOk()
            ->assertSee('Pasien');

        $this->get(route('periksa-pasien.create', $daftarPoli->id))
            ->assertOk()
            ->assertSee('Pilih Obat');

        $this->post(route('periksa-pasien.store'), [
            'id_daftar_poli' => $daftarPoli->id,
            'obat_json' => json_encode([(string) $obat->id]),
            'biaya_periksa' => 1,
            'catatan' => 'Istirahat cukup',
        ])->assertRedirect(route('periksa-pasien.index'));

        $this->assertDatabaseHas('periksa', [
            'id_daftar_poli' => $daftarPoli->id,
            'catatan' => 'Istirahat cukup',
            'biaya_periksa' => 155000,
        ]);

        $this->get(route('riwayat-pasien.index'))
            ->assertOk()
            ->assertSee('Istirahat cukup')
            ->assertSee('Riwayat Pasien');

        $periksa = \App\Models\Periksa::firstOrFail();
        $this->get(route('riwayat-pasien.show', $periksa->id))
            ->assertOk()
            ->assertSee('Paracetamol')
            ->assertSee('Demam');
    }

    private function buatKlinik(): array
    {
        $poli = Poli::create([
            'nama_poli' => 'Umum',
            'keterangan' => 'Poli umum',
        ]);
        $pasien = User::create([
            'nama' => 'Pendaftar',
            'email' => 'pendaftar@example.com',
            'password' => 'password',
            'role' => 'pasien',
        ]);
        $dokter = User::create([
            'nama' => 'Dokter',
            'email' => 'dokter@example.com',
            'password' => 'password',
            'role' => 'dokter',
            'id_poli' => $poli->id,
        ]);
        $jadwal = JadwalPeriksa::create([
            'id_dokter' => $dokter->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'status' => true,
        ]);

        return [$pasien, $dokter, $jadwal];
    }
}
