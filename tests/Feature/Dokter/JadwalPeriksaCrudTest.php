<?php

namespace Tests\Feature\Dokter;

use App\Models\JadwalPeriksa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalPeriksaCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_manage_only_their_own_schedules(): void
    {
        $this->withoutVite();

        $dokter = $this->buatDokter('dokter@example.com');
        $dokterLain = $this->buatDokter('dokter-lain@example.com');
        $dokterId = (int) $dokter->getKey();
        $jadwalDokterLain = JadwalPeriksa::create([
            'id_dokter' => $dokterLain->getKey(),
            'hari' => 'Selasa',
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'status' => true,
        ]);

        $this->actingAs($dokter)
            ->get(route('jadwal-periksa.index'))
            ->assertOk()
            ->assertDontSee('Selasa');

        $this->post(route('jadwal-periksa.store'), [
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'status' => '1',
        ])->assertRedirect(route('jadwal-periksa.index'));

        $jadwal = JadwalPeriksa::where('id_dokter', $dokterId)->firstOrFail();
        $this->get(route('jadwal-periksa.index'))
            ->assertSee('Senin')
            ->assertDontSee('Selasa');
        $this->assertDatabaseHas('jadwal_periksa', [
            'id' => $jadwal->getKey(),
            'id_dokter' => $dokterId,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'status' => 1,
        ]);

        $jadwalId = $jadwal->getKey();
        $this->get(route('jadwal-periksa.edit', $jadwalId))
            ->assertOk()
            ->assertSee('Senin');

        $this->put(route('jadwal-periksa.update', $jadwalId), [
            'hari' => 'Rabu',
            'jam_mulai' => '10:00',
            'jam_selesai' => '13:00',
            'status' => '0',
        ])->assertRedirect(route('jadwal-periksa.index'));

        $this->assertDatabaseHas('jadwal_periksa', [
            'id' => $jadwalId,
            'hari' => 'Rabu',
            'jam_mulai' => '10:00',
            'jam_selesai' => '13:00',
            'status' => 0,
        ]);

        $jadwalLainId = $jadwalDokterLain->getKey();
        $this->get(route('jadwal-periksa.edit', $jadwalLainId))->assertNotFound();
        $this->put(route('jadwal-periksa.update', $jadwalLainId), [
            'hari' => 'Kamis',
            'jam_mulai' => '10:00',
            'jam_selesai' => '13:00',
            'status' => '1',
        ])->assertNotFound();
        $this->delete(route('jadwal-periksa.destroy', $jadwalLainId))->assertNotFound();

        $this->delete(route('jadwal-periksa.destroy', $jadwalId))
            ->assertRedirect(route('jadwal-periksa.index'));
        $this->assertDatabaseMissing('jadwal_periksa', ['id' => $jadwalId]);
        $this->assertDatabaseHas('jadwal_periksa', ['id' => $jadwalLainId]);
    }

    public function test_schedule_requires_valid_day_and_increasing_times(): void
    {
        $dokter = $this->buatDokter('dokter@example.com');

        $this->actingAs($dokter)
            ->post(route('jadwal-periksa.store'), [
                'hari' => 'Funday',
                'jam_mulai' => '12:00',
                'jam_selesai' => '10:00',
                'status' => 'invalid',
            ])
            ->assertSessionHasErrors(['hari', 'jam_selesai', 'status']);

        $this->assertDatabaseCount('jadwal_periksa', 0);
    }

    private function buatDokter(string $email): User
    {
        return User::create([
            'nama' => 'Dokter',
            'email' => $email,
            'password' => 'password',
            'role' => 'dokter',
        ]);
    }
}
