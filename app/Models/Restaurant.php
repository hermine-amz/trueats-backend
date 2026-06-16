<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'nom', 'adresse', 'quartier', 'categorie', 'type_cuisine',
    'latitude', 'longitude', 'qr_code_identifier', 'gerant_id',
    'superficie', 'est_valide', 'bloque_jusqua', 'est_archive',
    'logo_url', 'photo_url',
    'cip_url', 'ifu_numero', 'ifu_attestation_url',
    'rccm_numero', 'rccm_extrait_url', 'motif_rejet',
])]
class Restaurant extends Model
{
    use HasFactory;

    protected $appends = ['rayon_validation'];

    protected function casts(): array
    {
        return [
            'est_valide' => 'boolean',
            'est_archive' => 'boolean',
            'bloque_jusqua' => 'datetime',
        ];
    }

    public function getRayonValidationAttribute()
    {
        $superficie = $this->superficie ?? 100;
        $rayonGeometrique = sqrt($superficie / pi());
        return max(15, (int) round($rayonGeometrique + 15));
    }

    public function estBloque(): bool
    {
        return $this->bloque_jusqua && $this->bloque_jusqua->isFuture();
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
