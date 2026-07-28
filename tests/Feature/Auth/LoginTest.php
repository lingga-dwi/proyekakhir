<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_uses_simple_daiku_layout(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Selamat Datang')
            ->assertSee('Silakan login di sini.')
            ->assertDontSee('Portal pelanggan')
            ->assertDontSee('Masuk untuk melanjutkan pengelolaan proyek interior Anda.')
            ->assertSee('images/hero/daiku-home-hero.jpg', false)
            ->assertSee('Tampilkan password')
            ->assertSee('Lupa password?');
    }

    public function test_login_redirects_home_without_welcome_notification(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertRedirect(route('home'))
            ->assertSessionMissing('success');

        $this->assertAuthenticatedAs($user);
    }

    public function test_repeated_login_attempts_are_rate_limited(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('login'), [
                'email' => 'unknown@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        $this->post(route('login'), [
            'email' => 'unknown@example.com',
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    }
}
