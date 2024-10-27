<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
            public function up()
            {
                // Genders Table
                Schema::create('genders', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->string('slug')->unique();
                    $table->timestamps();
                });
        
                // Tournament States Table
                Schema::create('tournament_states', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->string('slug')->unique();
                    $table->text('description')->nullable();
                    $table->timestamps();
                });
        
                // Players Table
                Schema::create('players', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->foreignId('gender_id')->constrained('genders');
                    $table->integer('ability')->default(0);
                    // almancena json
                    $table->timestamps();
                });
        
                // Tournaments Table
                Schema::create('tournaments', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->foreignId('gender_id')->constrained('genders');
                    $table->foreignId('state_id')->constrained('tournament_states');
                    $table->integer('number_players');
                    $table->foreignId('winner_id')->nullable()->constrained('players');
                    $table->foreignId('tournament_id')->constrained('tournamenta');
                    $table->timestamps();
                });
        
                // Attributes Table
                Schema::create('attributes', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->foreignId('gender_id')->constrained('genders');
                    $table->string('slug')->unique();
                    $table->timestamps();
                });
        
                // Player Attributes Table
                Schema::create('player_attributes', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('player_id')->constrained('players');
                    $table->foreignId('attribute_id')->constrained('attributes');
                    $table->integer('points');
                    $table->timestamps();
                });
        
                // Tournament Players State Table
                Schema::create('tournament_player_states', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->string('slug')->unique();
                    $table->text('description')->nullable();
                    $table->timestamps();
                });
        
                // Tournament Players Table
                Schema::create('tournament_players', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('player_id')->constrained('players');
                    $table->foreignId('tournament_id')->constrained('tournaments');
                    $table->foreignId('state_id')->constrained('tournament_player_states');
                    $table->timestamps();
                });
        
                // Plays Table
                Schema::create('plays', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('player1_id')->constrained('players');
                    $table->foreignId('player2_id')->constrained('players');
                    $table->foreignId('winner_id')->nullable()->constrained('players');
                    $table->foreignId('loser_id')->nullable()->constrained('players');
                    $table->integer('round');
                    $table->json('details')->nullable();
                    $table->timestamps();
                });
            }
        
            /**
             * Reverse the migrations.
             *
             * @return void
             */
            public function down()
            {
                Schema::dropIfExists('plays');
                Schema::dropIfExists('tournament_players');
                Schema::dropIfExists('tournament_player_states');
                Schema::dropIfExists('player_attributes');
                Schema::dropIfExists('attributes');
                Schema::dropIfExists('tournaments');
                Schema::dropIfExists('players');
                Schema::dropIfExists('tournament_states');
                Schema::dropIfExists('genders');
            }

};
