<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;

#[Fillable(['nom', 'prenom', 'email', 'password', 'telephone', 'role', 'compte_active', 'sexe'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'compte_active' => 'boolean',
        ];
    }

    public function restaurants()
    {
        return $this->hasMany(Restaurant::class, 'gerant_id');
    }

    public function avis()
    {
        return $this->hasMany(Avis::class);
    }

    public function signals()
    {
        return $this->hasMany(Signal::class);
    }

    public function explorations()
    {
        return $this->hasMany(Exploration::class);
    }
}
