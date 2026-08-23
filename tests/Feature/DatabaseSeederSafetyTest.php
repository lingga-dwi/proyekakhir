<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_seeder_does_not_create_dummy_users_or_projects(): void
    {
        $this->seed();

        $this->assertDatabaseCount('pemesanan', 0);
        $this->assertDatabaseMissing('users', ['email' => 'budi.santoso@gmail.com']);
        $this->assertDatabaseMissing('users', ['email' => 'maya.indira@gmail.com']);
    }
}
