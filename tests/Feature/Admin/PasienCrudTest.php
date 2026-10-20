<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasienCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_and_add_a_patient(): void
    {
        $this->withoutVite();

        $admin = User::create([
            'nama' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('pasien.create'))
            ->assertOk()
            ->assertSee('Tambah Pasien');

        $this->post(route('pasien.store'), [
            'nama' => 'Budi Santoso',
            'alamat' => 'Jl. Melati No. 10',
            'no_ktp' => '1234567890123456',
            'no_hp' => '081234567890',
            'email' => 'budi@example.com',
            'password' => 'password',
        ])->assertRedirect(route('pasien.index'));

        $this->assertDatabaseHas('users', [
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'role' => 'pasien',
        ]);
    }
}
