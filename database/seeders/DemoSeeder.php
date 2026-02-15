<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@example'],
            [
                'name' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        // Crear 5 notas
        $user->notes()->delete(); // limpiar si existen

        for ($i = 1; $i <= 5; $i++) {
            $user->notes()->create([
                'title' => "Nota $i",
                'content' => "Contenido de la nota número $i"
            ]);
        }
    }
}
