<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'gender_id', 'slug'];

    // Relación con Gender
    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    // Relación con Players (many-to-many)
    public function players()
    {
        return $this->belongsToMany(Player::class, 'player_attributes')
                    ->withPivot('points')
                    ->withTimestamps();
    }
}
