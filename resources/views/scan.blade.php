@php
    $resolveImage = function ($url) {
        if (empty($url)) {
            return null;
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return str_replace('/storage/', '/api/storage/', $url);
        }
        if (str_starts_with($url, '/storage/')) {
            return '/api/storage/' . substr($url, 9);
        }
        if (str_starts_with($url, 'storage/')) {
            return '/api/storage/' . substr($url, 8);
        }
        return '/api/storage/' . $url;
    };
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($restaurant) ? $restaurant->nom . ' - Menu & Avis' : 'TruEats' }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --terracotta: #C0552D;
            --sauge: #75B58D;
            --creme: #FAF6EE;
            --marron-fonce: #2E1E17;
            --gris-bordure: #E5E0D8;
            --creme-fonce: #F3ECE0;
            --vert-clair: #E2F0E7;
            --rouge-clair: #FFFFEBEE;
            --rouge-signalement: #C62828;
            --gris-texte: #7A6F68;
            --orange-clair: #FBE9E7;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--creme);
            color: var(--marron-fonce);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px 16px 80px 16px;
        }

        /* En-tête */
        header {
            text-align: center;
            padding: 20px 0 30px 0;
        }

        header img.app-logo {
            height: 48px;
            object-fit: contain;
        }

        /* Fiche Etablissement */
        .card {
            background-color: #FFFFFF;
            border-radius: 24px;
            border: 1px solid var(--gris-bordure);
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(46, 30, 23, 0.04);
            margin-bottom: 24px;
            position: relative;
        }

        .cover-container {
            height: 160px;
            background-color: var(--creme-fonce);
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .logo-container {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background-color: #FFFFFF;
            border: 3px solid #FFFFFF;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            position: absolute;
            left: 20px;
            bottom: -35px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .logo-placeholder {
            width: 100%;
            height: 100%;
            background-color: var(--orange-clair);
            color: var(--terracotta);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-family: 'Lora', serif;
            font-weight: bold;
        }

        .restaurant-info {
            padding: 50px 20px 20px 20px;
        }

        .restaurant-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .restaurant-title {
            font-family: 'Lora', serif;
            font-size: 24px;
            font-weight: bold;
            color: var(--marron-fonce);
            line-height: 1.2;
        }

        .restaurant-meta {
            font-size: 13px;
            color: var(--gris-texte);
            margin-bottom: 16px;
            font-weight: 500;
        }

        .info-row {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .info-row i {
            color: var(--terracotta);
            margin-right: 12px;
            font-size: 16px;
            margin-top: 2px;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 11px;
            color: var(--gris-texte);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: bold;
            margin-bottom: 1px;
        }

        .info-val {
            color: var(--marron-fonce);
        }

        /* Boutons Actions Visiteur */
        .actions-row {
            display: flex;
            gap: 12px;
            margin-bottom: 28px;
        }

        .btn-action {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px 12px;
            border-radius: 20px;
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-action i {
            font-size: 24px;
            margin-bottom: 6px;
        }

        .btn-menu {
            background-color: var(--terracotta);
            color: #FFFFFF;
            box-shadow: 0 4px 14px rgba(192, 85, 45, 0.25);
        }

        .btn-menu.inactive {
            background-color: var(--creme-fonce);
            color: var(--gris-texte);
            box-shadow: none;
        }

        .btn-review {
            background-color: var(--creme-fonce);
            color: var(--marron-fonce);
            border: 1px solid var(--gris-bordure);
        }

        .btn-review.active {
            background-color: var(--terracotta);
            color: #FFFFFF;
            box-shadow: 0 4px 14px rgba(192, 85, 45, 0.25);
            border: none;
        }

        /* Onglets Contenu */
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Section Menu */
        .menu-category-title {
            font-size: 12px;
            color: var(--gris-texte);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: bold;
            margin: 24px 0 12px 4px;
            background-color: var(--creme-fonce);
            padding: 8px 14px;
            border-radius: 12px;
            display: inline-block;
        }

        .dish-card {
            background-color: #FFFFFF;
            border-radius: 20px;
            border: 1px solid var(--gris-bordure);
            padding: 16px;
            margin-bottom: 12px;
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .dish-info {
            flex: 1;
        }

        .dish-title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
            gap: 8px;
        }

        .dish-title {
            font-size: 16px;
            font-weight: bold;
            color: var(--marron-fonce);
        }

        .dish-price {
            font-size: 15px;
            font-weight: bold;
            color: var(--terracotta);
            white-space: nowrap;
        }

        .dish-desc {
            font-size: 13px;
            color: var(--gris-texte);
            line-height: 1.4;
        }

        .dish-img {
            width: 72px;
            height: 72px;
            border-radius: 12px;
            object-fit: cover;
            background-color: var(--creme-fonce);
        }

        /* Section Donner son Avis */
        .download-box {
            text-align: center;
            padding: 30px 20px;
        }

        .download-icon {
            width: 64px;
            height: 64px;
            background-color: var(--orange-clair);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
        }

        .download-icon i {
            font-size: 28px;
            color: var(--terracotta);
        }

        .download-title {
            font-family: 'Lora', serif;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .download-desc {
            font-size: 14px;
            color: var(--gris-texte);
            line-height: 1.5;
            margin-bottom: 28px;
        }

        .store-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 280px;
            margin: 0 auto;
        }

        .store-btn {
            background-color: var(--marron-fonce);
            color: #FFFFFF;
            text-decoration: none;
            border-radius: 14px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            text-align: left;
            transition: background-color 0.2s ease;
        }

        .store-btn:hover {
            background-color: #000000;
        }

        .store-btn i {
            font-size: 26px;
            margin-right: 14px;
        }

        .store-btn-text {
            display: flex;
            flex-direction: column;
        }

        .store-btn-sub {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gris-bordure);
            margin-bottom: 1px;
        }

        .store-btn-main {
            font-size: 15px;
            font-weight: bold;
        }

        /* Error state */
        .error-card {
            text-align: center;
            padding: 40px 20px;
            border: 1.5px solid var(--terracotta);
        }

        .error-icon {
            font-size: 48px;
            color: var(--terracotta);
            margin-bottom: 16px;
        }

        .error-title {
            font-family: 'Lora', serif;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .error-desc {
            color: var(--gris-texte);
            font-size: 14px;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- En-tête de l'application -->
    <header>
        <img class="app-logo" src="{{ asset('images/logo_transparent.png') }}" alt="TruEats Logo">
    </header>

    @if(isset($error))
        <!-- Cas d'erreur : restaurant introuvable ou indisponible -->
        <div class="card error-card">
            <div class="error-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="error-title">Établissement indisponible</div>
            <div class="error-desc">{{ $error }}</div>
            <div style="font-size: 12px; color: var(--gris-texte);">Merci d'utiliser TruEats pour vos explorations culinaires.</div>
        </div>
    @else
        <!-- Fiche Restaurant -->
        <div class="card">
            <!-- Image de couverture -->
            <div class="cover-container" style="background-image: url('{{ $restaurant->photo_url ? $resolveImage($restaurant->photo_url) : '' }}');">
                <!-- Logo du restaurant -->
                <div class="logo-container">
                    @if($restaurant->logo_url)
                        <img src="{{ $resolveImage($restaurant->logo_url) }}" alt="{{ $restaurant->nom }}">
                    @else
                        <div class="logo-placeholder">
                            {{ substr($restaurant->nom, 0, 1) }}
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Informations de base -->
            <div class="restaurant-info">
                <div class="restaurant-header-row">
                    <div class="restaurant-title">{{ $restaurant->nom }}</div>
                </div>
                
                <div class="restaurant-meta">
                    {{ $restaurant->categorie }} · {{ $restaurant->type_cuisine }}
                </div>
                
                <!-- Détail Itinéraire -->
                <div class="info-row">
                    <i class="bi bi-geo-alt"></i>
                    <div class="info-content">
                        <div class="info-label">Itinéraire</div>
                        <div class="info-val">{{ $restaurant->adresse }} ({{ $restaurant->quartier }})</div>
                    </div>
                </div>

                <!-- Détail Téléphone -->
                <div class="info-row">
                    <i class="bi bi-telephone"></i>
                    <div class="info-content">
                        <div class="info-label">Téléphone</div>
                        <div class="info-val">{{ $restaurant->telephone ?? 'Non renseigné' }}</div>
                    </div>
                </div>

                <!-- Détail Horaires -->
                <div class="info-row">
                    <i class="bi bi-clock"></i>
                    <div class="info-content">
                        <div class="info-label">Horaires d'ouverture</div>
                        <div class="info-val">{{ $restaurant->horaires ?? 'Non renseigné' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons Actions Visiteur -->
        <div class="actions-row">
            <button id="btnTabMenu" class="btn-action btn-menu" onclick="switchTab('menu')">
                <i class="bi bi-journal-richtext"></i>
                Voir le menu
            </button>
            <button id="btnTabReview" class="btn-action btn-review" onclick="switchTab('review')">
                <i class="bi bi-star"></i>
                Donner mon avis
            </button>
        </div>

        <!-- Contenu Onglet 1 : Menu -->
        <div id="tabMenuContent" class="tab-content active">
            @if($groupedPlats->isEmpty())
                <div class="card" style="padding: 24px; text-align: center; color: var(--gris-texte);">
                    <i class="bi bi-egg-fried" style="font-size: 24px; color: var(--terracotta); margin-bottom: 8px; display: block;"></i>
                    Aucun plat disponible pour le moment dans cet établissement.
                </div>
            @else
                @foreach($groupedPlats as $categoryName => $plats)
                    <div class="menu-category-title">{{ $categoryName }}</div>
                    @foreach($plats as $plat)
                        <div class="dish-card">
                            <div class="dish-info">
                                <div class="dish-title-row">
                                    <div class="dish-title">{{ $plat->nom }}</div>
                                    <div class="dish-price">{{ number_format($plat->prix, 0, ',', ' ') }} FCFA</div>
                                </div>
                                <div class="dish-desc">{{ $plat->description }}</div>
                            </div>
                            @if($plat->image_url)
                                <img class="dish-img" src="{{ $resolveImage($plat->image_url) }}" alt="{{ $plat->nom }}">
                            @endif
                        </div>
                    @endforeach
                @endforeach
            @endif
        </div>

        <!-- Contenu Onglet 2 : Donner son Avis -->
        <div id="tabReviewContent" class="tab-content">
            <div class="card download-box">
                <div class="download-icon">
                    <i class="bi bi-phone"></i>
                </div>
                <div class="download-title">Publier un avis vérifié</div>
                <div class="download-desc">
                    Pour garantir des avis 100% fiables et éviter les abus, TruEats exige une validation physique par double vérification GPS au sein de l'établissement <strong>{{ $restaurant->nom }}</strong>.
                    <br><br>
                    Téléchargez notre application mobile officielle pour soumettre votre avis en direct.
                </div>
                
                <div class="store-buttons">
                    <!-- Bouton Google Play -->
                    <a href="#" class="store-btn">
                        <i class="bi bi-play-btn-fill"></i>
                        <div class="store-btn-text">
                            <span class="store-btn-sub">Disponible sur</span>
                            <span class="store-btn-main">Google Play</span>
                        </div>
                    </a>
                    
                    <!-- Bouton App Store -->
                    <a href="#" class="store-btn">
                        <i class="bi bi-apple"></i>
                        <div class="store-btn-text">
                            <span class="store-btn-sub">Télécharger dans l'</span>
                            <span class="store-btn-main">App Store</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    @endif

</div>

<script>
    function switchTab(tabName) {
        // Obtenir les boutons et contenus
        const btnMenu = document.getElementById('btnTabMenu');
        const btnReview = document.getElementById('btnTabReview');
        const contentMenu = document.getElementById('tabMenuContent');
        const contentReview = document.getElementById('tabReviewContent');
        
        if (!btnMenu || !btnReview || !contentMenu || !contentReview) return;

        if (tabName === 'menu') {
            // Activer onglet Menu
            btnMenu.className = 'btn-action btn-menu';
            btnReview.className = 'btn-action btn-review';
            
            contentMenu.classList.add('active');
            contentReview.classList.remove('active');
        } else {
            // Activer onglet Review
            btnMenu.className = 'btn-action btn-review';
            btnReview.className = 'btn-action btn-menu'; // style terracotta actif
            
            contentMenu.classList.remove('active');
            contentReview.classList.add('active');
        }
    }
</script>
</body>
</html>
