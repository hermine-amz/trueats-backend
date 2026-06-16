<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class WebRestaurantController extends Controller
{
    public function scanRestaurant($qrCode)
    {
        $restaurant = Restaurant::with(['plats.category'])
            ->where('qr_code_identifier', $qrCode)
            ->first();

        $error = null;
        $groupedPlats = collect();

        if (!$restaurant) {
            $error = "Ce code QR ne correspond à aucun établissement enregistré.";
        } else if (!$restaurant->est_valide) {
            $error = "Cet établissement est en cours de validation administrative.";
        } else if ($restaurant->estBloque()) {
            $error = "Cet établissement est temporairement suspendu.";
        } else if ($restaurant->est_archive) {
            $error = "Cet établissement est actuellement masqué.";
        } else {
            // Regroupe par catégorie de plat
            $groupedPlats = $restaurant->plats->where('disponible', true)->groupBy(function($plat) {
                return $plat->category ? $plat->category->libelle : 'Autre';
            });
        }

        return view('scan', compact('restaurant', 'groupedPlats', 'error'));
    }
}
