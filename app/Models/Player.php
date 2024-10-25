<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'gender_id', 'ability'];

    // Relación con Gender
    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    // Relación con Attributes (many-to-many)
    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'player_attributes')
                    ->withPivot('points')
                    ->withTimestamps();
    }

    // Relación con Tournaments (many-to-many)
    public function tournaments()
    {
        return $this->belongsToMany(Tournament::class, 'tournament_players')
                    ->withPivot('state_id')
                    ->withTimestamps();
    }

    // Relación con Plays (Partidas jugadas)
    public function playsAsPlayer1()
    {
        return $this->hasMany(Play::class, 'player1_id');
    }

    public function playsAsPlayer2()
    {
        return $this->hasMany(Play::class, 'player2_id');
    }

    // Relación con Plays (ganador/perdedor)
    public function wins()
    {
        return $this->hasMany(Play::class, 'winner_id');
    }

    public function losses()
    {
        return $this->hasMany(Play::class, 'loser_id');
    }
}
