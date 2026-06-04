<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['libelle'])]
class Category extends Model
{
    use HasFactory;

    public function plats()
    {
        return $this->hasMany(Plat::class);
    }
}
