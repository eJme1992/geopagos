<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerAttribute extends Model
{
    use HasFactory;

    protected $fillable = ['player_id', 'attribute_id', 'points'];

    // Relación con Player
    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    // Relación con Attribute
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
