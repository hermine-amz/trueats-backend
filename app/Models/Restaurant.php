<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['nom', 'adresse', 'latitude', 'longitude', 'qr_code_identifier', 'gerant_id', 'superficie'])]
class Restaurant extends Model
{
    use HasFactory;

    protected $appends = ['rayon_validation'];

    public function getRayonValidationAttribute()
    {
        $superficie = $this->superficie ?? 100;
        $rayonGeometrique = sqrt($superficie / pi());
        return max(15, (int) round($rayonGeometrique + 15));
    }

    public function gerant()
    {
        return $this->belongsTo(User::class, 'gerant_id');
    }

    public function plats()
    {
        return $this->hasMany(Plat::class);
    }

    public function avis()
    {
        return $this->hasMany(Avis::class);
    }
}
