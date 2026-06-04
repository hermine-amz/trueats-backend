<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['note', 'commentaire', 'date_visite', 'lat_client', 'long_client', 'est_publie', 'user_id', 'restaurant_id'])]
class Avis extends Model
{
    use HasFactory;

    protected $table = 'avis';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function signals()
    {
        return $this->hasMany(Signal::class);
    }
}
