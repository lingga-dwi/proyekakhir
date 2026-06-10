<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // Admin users
        User::updateOrCreate(['email' => 'admin@daikuinterior.com'], [
            'nama' => 'Administrator',
            'password' => $password,
            'role' => 'admin',
            'alamat' => 'Jakarta, Indonesia',
            'no_telp' => '081234567890',
        ]);

        User::updateOrCreate(['email' => 'lingga@daikuinterior.com'], [
            'nama' => 'Lingga',
            'password' => $password,
            'role' => 'admin',
            'alamat' => 'Jakarta, Indonesia',
            'no_telp' => '081234567899',
        ]);

        // Designer users
        User::updateOrCreate(['email' => 'ahmad.fauzi@daikuinterior.com'], [
            'nama' => 'Ahmad Fauzi',
            'password' => $password,
            'role' => 'designer',
            'alamat' => 'Bandung, Indonesia',
            'no_telp' => '081234567891',
        ]);

        User::updateOrCreate(['email' => 'sari.dewi@daikuinterior.com'], [
            'nama' => 'Sari Dewi',
            'password' => $password,
            'role' => 'designer',
            'alamat' => 'Surabaya, Indonesia',
            'no_telp' => '081234567892',
        ]);

        // Sample customers
        User::updateOrCreate(['email' => 'budi.santoso@gmail.com'], [
            'nama' => 'Budi Santoso',
            'password' => $password,
            'role' => 'pelanggan',
            'alamat' => 'Jl. Merdeka No. 123, Jakarta',
            'no_telp' => '081234567893',
        ]);

        User::updateOrCreate(['email' => 'maya.indira@gmail.com'], [
            'nama' => 'Maya Indira',
            'password' => $password,
            'role' => 'pelanggan',
            'alamat' => 'Jl. Sudirman No. 456, Bandung',
            'no_telp' => '081234567894',
        ]);
    }
}
