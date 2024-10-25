<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       // Insert Genders
        DB::table('genders')->insert([
            ['name' => 'Male', 'slug' => 'male'],
            ['name' => 'Female', 'slug' => 'female'],
        ]);

        // Insert Tournament States
        DB::table('tournament_states')->insert([
            ['name' => 'Creado', 'slug' => 'created', 'description' => 'Tournament is created'],
            ['name' => 'Completo', 'slug' => 'complete', 'description' => 'Tournament is complete'],
            ['name' => 'Finalizado', 'slug' => 'finished', 'description' => 'Tournament is finished'],
        ]);

        // Get gender IDs dynamically
        $maleGenderId = DB::table('genders')->where('slug', 'male')->value('id');
        $femaleGenderId = DB::table('genders')->where('slug', 'female')->value('id');

        // Insert Attributes with dynamic gender IDs
        DB::table('attributes')->insert([
            ['name' => 'Fuerza', 'gender_id' => $maleGenderId, 'slug' => 'strength'],
            ['name' => 'Velocidad', 'gender_id' => $maleGenderId, 'slug' => 'speed'],
            ['name' => 'Tiempo de Reacción', 'gender_id' => $femaleGenderId, 'slug' => 'reaction_time'],
        ]);

        // Insert Tournament Player States
        DB::table('tournament_player_states')->insert([
            ['name' => 'Pendiente', 'slug' => 'pending', 'description' => 'Player is pending'],
            ['name' => 'Ganador', 'slug' => 'winner', 'description' => 'Player is winner'],
            ['name' => 'Perdedor', 'slug' => 'loser', 'description' => 'Player is loser'],
        ]);
    }
}
