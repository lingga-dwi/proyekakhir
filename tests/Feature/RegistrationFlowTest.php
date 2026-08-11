<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_only_requires_identity_and_password(): void
    {
        $response = $this->post(route('register.post'), [
            'nama' => 'Pelanggan Baru',
            'email' => 'pelanggan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'pelanggan@example.com',
            'alamat' => null,
            'no_telp' => null,
        ]);
    }

    public function test_registration_form_does_not_show_address_or_phone_fields(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('name="alamat"', false)
            ->assertDontSee('name="no_telp"', false);
    }
}
