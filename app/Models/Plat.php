<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['nom', 'description', 'prix', 'disponible', 'restaurant_id', 'categorie_id', 'image_url'])]
class Plat extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'disponible' => 'boolean',
            'prix' => 'float',
        ];
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'categorie_id');
    }
}
