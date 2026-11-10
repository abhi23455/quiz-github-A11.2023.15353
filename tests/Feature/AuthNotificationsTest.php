<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_registration_shows_a_green_notification_on_login_page(): void
    {
        $this->withoutVite();

        $response = $this->post(route('register.store'), [
            'nama' => 'Pasien Baru',
            'alamat' => 'Jl. Sehat',
            'no_ktp' => '1234567890123456',
            'no_hp' => '08123456789',
            'email' => 'pasien@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHas('success', 'Registrasi berhasil! Silakan login.');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('alert-success')
            ->assertSee('Registrasi berhasil! Silakan login.');
    }

    public function test_successful_login_shows_a_green_notification_on_dashboard(): void
    {
        $this->withoutVite();

        User::create([
            'nama' => 'Pasien',
            'alamat' => 'Jl. Sehat',
            'no_ktp' => '1234567890123456',
            'no_hp' => '08123456789',
            'role' => 'pasien',
            'email' => 'pasien@example.com',
            'password' => 'password',
        ]);

        $this->post(route('login'), [
            'email' => 'pasien@example.com',
            'password' => 'password',
        ])->assertRedirect(route('pasien.dashboard'));

        $this->get(route('pasien.dashboard'))
            ->assertOk()
            ->assertSee('alert-success')
            ->assertSee('Login berhasil! Selamat datang.');
    }
}
