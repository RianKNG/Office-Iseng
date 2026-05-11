<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
   // database/factories/UserFactory.php
public function definition(): array
{
    return [
        'username' => fake()->userName(),
        'password_hash' => bcrypt('password'),
        'nama_lengkap' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'jabatan' => fake()->jobTitle(),
        
        // ✅ ENUM yang valid:
        'struktur' => fake()->randomElement(['pusat', 'cabang']),
        'unit_kerja' => fake()->randomElement([
            'keuangan', 'pelayanan', 'teknikprod', 'perencanaan', 'umum'
        ]),
        
        'level' => fake()->randomElement(['staff', 'kasubag_kasie', 'kabag_kacab', 'dirut']),
        'status' => 'aktif',
        'signature' => null,
    ];
}