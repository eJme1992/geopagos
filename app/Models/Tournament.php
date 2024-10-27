<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'gender_id', 'state_id', 'number_players','winner_id'];

    // agregar datos de funcions al modelo
    protected $appends = ['winner_name','state_name','plays'];

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

    // play 
    public function plays()
    {
        return $this->hasMany(Play::class);
    }

    // Relación con Player (winner)
    public function winner()
    {
        return $this->belongsTo(Player::class, 'winner_id');
    }

    public function getWinnerNameAttribute()
    {
        return $this->winner ? $this->winner->name : '';
    }

    public function getStateNameAttribute()
    {
        return $this->state ? $this->state->name : '';
    }

    // play
    public function getPlaysAttribute()
    {
        return $this->plays()->get();
    }
}
