<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    // Relación con Players
    public function players()
    {
        return $this->hasMany(Player::class);
    }

    // Relación con Tournaments
    public function tournaments()
    {
        return $this->hasMany(Tournament::class);
    }

    // Relación con Attributes
    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }
}
