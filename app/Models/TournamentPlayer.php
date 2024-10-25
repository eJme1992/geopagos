<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentPlayer extends Model
{
    use HasFactory;

    protected $fillable = ['player_id', 'tournament_id', 'state_id'];

    // Relación con Player
    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    // Relación con Tournament
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    // Relación con TournamentPlayerState
    public function state()
    {
        return $this->belongsTo(TournamentState::class);
    }
}
