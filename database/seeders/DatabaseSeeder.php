<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Crea un usuario Administrador por defecto para que
     * cualquier persona que clone el repo pueda iniciar sesión.
     */
    public function run(): void
    {
        // Crear el usuario administrador por defecto (solo si no existe)
        if (!Usuario::where('usuario', 'admin')->exists()) {
            Usuario::create([
                'nombre'    => 'Administrador',
                'usuario'   => 'admin',
                'password'  => Hash::make('Adm!n#2026$SecureX9'),
                'rol'       => 'Administrador',
                'creado_en' => now(),
            ]);
        }

        // Crear usuario Almacenero por defecto (solo si no existe)
        if (!Usuario::where('usuario', 'almacenero')->exists()) {
            Usuario::create([
                'nombre'    => 'Almacenero Principal',
                'usuario'   => 'almacenero',
                'password'  => Hash::make('Alm@cen#2026'),
                'rol'       => 'Almacenero',
                'creado_en' => now(),
            ]);
        }

        // Crear usuario Supervisor por defecto (solo si no existe)
        if (!Usuario::where('usuario', 'supervisor')->exists()) {
            Usuario::create([
                'nombre'    => 'Supervisor General',
                'usuario'   => 'supervisor',
                'password'  => Hash::make('Sup3rv!sor#2026'),
                'rol'       => 'Supervisor',
                'creado_en' => now(),
            ]);
        }
    }
}
