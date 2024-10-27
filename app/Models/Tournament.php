<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'gender_id', 'state_id', 'number_players','winner_id'];

    protected $winth = ['players'];

    // Relación con Players (many-to-many)
    public function players()
    {
        return $this->belongsToMany(Player::class, 'tournament_players')
                    ->withPivot('state_id')
                    ->withTimestamps();
    }

    // Relación con Gender
    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    // Relación con TournamentState
    public function state()
    {
        return $this->belongsTo(TournamentState::class, 'state_id');
    }

    // Relación con Player (winner)
    public function winner()
    {
        return $this->belongsTo(Player::class, 'winner_id');
    }
}
