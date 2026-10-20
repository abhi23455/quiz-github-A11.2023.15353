<?php

namespace Tests\Feature\Admin;

use App\Models\Obat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObatCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_obat(): void
    {
        $this->withoutVite();

        $admin = User::create([
            'nama' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('obat.index'))
            ->assertOk();

        $this->post(route('obat.store'), [
            'nama_obat' => 'Paracetamol',
            'kemasan' => 'Tablet',
            'harga' => 5000,
        ])->assertRedirect(route('obat.index'));

        $obat = Obat::firstOrFail();
        $this->assertDatabaseHas('obat', [
            'nama_obat' => 'Paracetamol',
            'kemasan' => 'Tablet',
            'harga' => 5000,
        ]);

        $this->get(route('obat.edit', $obat))
            ->assertOk()
            ->assertSee('Paracetamol');

        $this->put(route('obat.update', $obat), [
            'nama_obat' => 'Paracetamol 500mg',
            'kemasan' => 'Strip',
            'harga' => 7500,
        ])->assertRedirect(route('obat.index'));

        $this->assertDatabaseHas('obat', [
            'nama_obat' => 'Paracetamol 500mg',
            'kemasan' => 'Strip',
            'harga' => 7500,
        ]);

        $this->delete(route('obat.destroy', $obat))
            ->assertRedirect(route('obat.index'));

        $this->assertDatabaseMissing('obat', ['nama_obat' => 'Paracetamol 500mg']);
    }

    public function test_obat_requires_validated_fields(): void
    {
        $admin = User::create([
            'nama' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post(route('obat.store'), [
                'nama_obat' => '',
                'kemasan' => str_repeat('a', 36),
                'harga' => -1,
            ])
            ->assertSessionHasErrors(['nama_obat', 'kemasan', 'harga']);

        $this->assertDatabaseCount('obat', 0);
    }
}
