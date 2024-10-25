<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Play extends Model
{
    use HasFactory;

    protected $fillable = ['player1_id', 'player2_id', 'winner_id', 'loser_id', 'round'];

    // Relación con Player (Jugador 1)
    public function player1()
    {
        return $this->belongsTo(Player::class, 'player1_id');
    }

    // Relación con Player (Jugador 2)
    public function player2()
    {
        return $this->belongsTo(Player::class, 'player2_id');
    }

    // Relación con Player (Ganador)
    public function winner()
    {
        return $this->belongsTo(Player::class, 'winner_id');
    }

    // Relación con Player (Perdedor)
    public function loser()
    {
        return $this->belongsTo(Player::class, 'loser_id');
    }
}
