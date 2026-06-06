<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Admin Madiffy',
            'email'    => 'admin@madiffy.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Vendedor Madiffy',
            'email'    => 'vendedor@madiffy.com',
            'password' => bcrypt('password'),
            'role'     => 'vendedor',
        ]);

        $this->call([
            ClientSeeder::class,
            ProductSeeder::class,
            QuotationSeeder::class,
        ]);
    }
}