<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            #SerieSeeder::class,
            #FunctionSerieSeeder::class,
            #ChapterSeeder::class,

            // Agrega aquí cualquier otro seeder que quieras ejecutar
        ]);
    }

}
