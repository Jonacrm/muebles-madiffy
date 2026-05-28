<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'name'    => 'Constructora del Noroeste SA de CV',
                'email'   => 'compras@constructoranor.com',
                'phone'   => '6621234567',
                'rfc'     => 'CNO850312AB1',
                'address' => 'Blvd. Solidaridad 1234, Hermosillo, Sonora',
            ],
            [
                'name'    => 'Hotel Sonora Inn',
                'email'   => 'administracion@sonorainn.com',
                'phone'   => '6627654321',
                'rfc'     => 'HSI920615CD2',
                'address' => 'Blvd. Kino 369, Hermosillo, Sonora',
            ],
            [
                'name'    => 'Restaurante El Mesquite',
                'email'   => 'contacto@elmesquite.com',
                'phone'   => '6629876543',
                'rfc'     => 'REM010203EF3',
                'address' => 'Calle Yáñez 456, Hermosillo, Sonora',
            ],
            [
                'name'    => 'Oficinas Corporativas Cima',
                'email'   => 'proyectos@cima.mx',
                'phone'   => '6621112233',
                'rfc'     => 'OCC150708GH4',
                'address' => 'Piso 8, Torre Cima, Hermosillo, Sonora',
            ],
            [
                'name'    => 'Juan Carlos Mendoza López',
                'email'   => 'jcmendoza@gmail.com',
                'phone'   => '6623334455',
                'rfc'     => 'MELJ880920IJ5',
                'address' => 'Calle Reforma 789, Hermosillo, Sonora',
            ],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}