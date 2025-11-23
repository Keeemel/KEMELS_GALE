<?php
/**
 * PAGE : CARTE INTERACTIVE
 * Visualisation du parc et des gérants avec design amélioré
 */

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/base_donnees/bdd.php';
require_once __DIR__ . '/includes/fonctions.php';
require_once __DIR__ . '/includes/authentification.php';

// Vérification de connexion (compatible si la fonction is_logged_in() existe ou via session brute)
$est_connecte = function_exists('is_logged_in') ? is_logged_in() : isset($_SESSION['user_id']);

// 1. Récupération des données
// Gérants
$gerants = [];
try {
    $stmt = $pdo->query("
        SELECT u.id, u.prenom, u.nom, gp.photo_url, gp.latitude, gp.longitude
        FROM user u
        LEFT JOIN gerant_profil gp ON u.id = gp.user_id
        WHERE u.role IN ('gerant','admin') AND gp.latitude IS NOT NULL
    ");
    $gerants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $gerants = []; }

// Éoliennes
$eoliennes = [];
try {
    $stmt = $pdo->query("
        SELECT e.id, e.identifiant, e.latitude, e.longitude, e.capacite_kw, e.etat,
               u.prenom, u.nom, gp.photo_url
        FROM eolienne e
        LEFT JOIN user u ON e.gerant_id = u.id
        LEFT JOIN gerant_profil gp ON u.id = gp.user_id
        WHERE e.latitude IS NOT NULL
    ");
    $eoliennes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $eoliennes = []; }

// Stats rapides pour le header
$totalEoliennes = count($eoliennes);
$activeEoliennes = count(array_filter($eoliennes, fn($e) => $e['etat'] !== 'Arrêt' && $e['etat'] !== 'Maintenance'));
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Custom Map CSS -->
<link rel="stylesheet" href="assets/css/carte.css" />

<main class="page-dashboard">
    <div class="container">
        
        <!-- En-tête style Dashboard -->
        <header class="section-head">
            <h1>Carte du parc éolien</h1>
            <p class="muted">
                Vue d'ensemble de la répartition géographique. <br>
                Actuellement <strong><?= $activeEoliennes ?></strong> turbines en production sur <strong><?= $totalEoliennes ?></strong>.
            </p>
            
            <!-- BOUTON CONDITIONNEL -->
            <div style="margin-top: 20px;">
                <?php if ($est_connecte): ?>
                    <a href="tableau_bord.php" class="btn btn--ghost" style="color:#0c3b2e; border-color:#0c3b2e;">
                        ← Retour au tableau de bord
                    </a>
                <?php else: ?>
                    <a href="connexion.php" class="btn btn--ghost" style="color:#0c3b2e; border-color:#0c3b2e;">
                        Se connecter en tant que gérant pour accéder au tableau de bord
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Carte Interactive -->
        <div class="map-card">
            <!-- Le conteneur de la carte -->
            <div id="map" style="width: 100%; height: 100%;"></div>

            <!-- Panneau de contrôle flottant -->
            <div class="map-controls">
                <h3>Filtres</h3>
                <button class="filter-btn active" onclick="filterMap('all', this)">
                    <span class="dot dot-gray"></span> Tout afficher
                </button>
                <button class="filter-btn" onclick="filterMap('ok', this)">
                    <span class="dot dot-green"></span> En fonctionnement
                </button>
                <button class="filter-btn" onclick="filterMap('nok', this)">
                    <span class="dot dot-red"></span> Maintenance / Arrêt
                </button>
            </div>
        </div>

    </div>
</main>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Données PHP vers JS
    const gerantsData = <?= json_encode($gerants, JSON_HEX_TAG) ?>;
    const eoliennesData = <?= json_encode($eoliennes, JSON_HEX_TAG) ?>;
    
    let map;
    let markersLayer = new L.LayerGroup(); // Groupe pour gérer les marqueurs
    let allMarkers = []; // Stockage local pour filtrage

    document.addEventListener('DOMContentLoaded', function () {
        // 1. Init Carte
        map = L.map('map', { zoomControl: false }).setView([46.5, 2], 6);
        
        // Contrôle de zoom en bas à droite pour ne pas gêner le header
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        // Tiles (Fond de carte)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        markersLayer.addTo(map);

        initMarkers();
    });

    function initMarkers() {
        const bounds = [];

        // --- MARQUEURS GÉRANTS ---
        gerantsData.forEach(g => {
            if(g.latitude && g.longitude) {
                const photo = g.photo_url ? g.photo_url : 'assets/images/gerants/default.jpg';
                
                // Création icône HTML
                const icon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div class="marker-gerant" style="background-image: url('${photo}')"></div>`,
                    iconSize: [48, 48],
                    iconAnchor: [24, 24],
                    popupAnchor: [0, -24]
                });

                const marker = L.marker([g.latitude, g.longitude], {icon: icon});
                
                // Popup HTML
                const popupContent = `
                    <div class="popup-header">
                        <h3>${g.prenom} ${g.nom}</h3>
                    </div>
                    <div class="popup-body">
                        <div class="popup-stat">
                            <span>Rôle</span>
                            <span>Gérant</span>
                        </div>
                        <div style="margin-top:10px; text-align:center;">
                            <a href="#" style="color:#0c3b2e; font-weight:600; text-decoration:none;">Voir profil</a>
                        </div>
                    </div>
                `;
                marker.bindPopup(popupContent);
                
                // Ajout au tableau (type: 'gerant')
                allMarkers.push({ marker: marker, type: 'gerant', status: 'ok' });
                bounds.push([g.latitude, g.longitude]);
            }
        });

        // --- MARQUEURS ÉOLIENNES ---
        eoliennesData.forEach(e => {
            if(e.latitude && e.longitude) {
                const isOk = (e.etat !== 'Arrêt' && e.etat !== 'Maintenance');
                const statusClass = isOk ? 'status-ok' : 'status-nok';
                const statusText = isOk ? 'En fonctionnement' : e.etat;
                
                // Icône SVG dynamique
                const svgIcon = isOk 
                    ? `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M12 2L2 22h20L12 2zm0 3.5L18.5 20H5.5L12 5.5z"/></svg>` // Eclair/Triangle
                    : `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>`; // Warning

                const icon = L.divIcon({
                    className: 'custom-marker',
                    html: `
                        <div class="marker-eolienne ${statusClass}">
                            <div class="marker-eolienne-inner">${svgIcon}</div>
                        </div>`,
                    iconSize: [36, 36],
                    iconAnchor: [18, 36],
                    popupAnchor: [0, -40]
                });

                const marker = L.marker([e.latitude, e.longitude], {icon: icon});

                // Popup
                const popupContent = `
                    <div class="popup-header" style="background:${isOk ? '#0c3b2e' : '#991b1b'}">
                        <h3>${e.identifiant}</h3>
                    </div>
                    <div class="popup-body">
                        <div class="popup-stat">
                            <span>Capacité</span>
                            <span>${e.capacite_kw} kW</span>
                        </div>
                        <div class="popup-stat">
                            <span>État</span>
                            <span class="status-badge ${statusClass}">${statusText}</span>
                        </div>
                        <div class="popup-stat">
                            <span>Gérant</span>
                            <span>${e.prenom ? (e.prenom + ' ' + e.nom) : 'Non assigné'}</span>
                        </div>
                    </div>
                `;
                marker.bindPopup(popupContent);

                allMarkers.push({ marker: marker, type: 'eolienne', status: isOk ? 'ok' : 'nok' });
                bounds.push([e.latitude, e.longitude]);
            }
        });

        // Afficher tous les marqueurs au départ
        updateMarkersDisplay('all');

        // Centrer la carte
        if(bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    // Fonction de filtrage
    function filterMap(criteria, btn) {
        // UI Boutons
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        updateMarkersDisplay(criteria);
    }

    function updateMarkersDisplay(criteria) {
        markersLayer.clearLayers();
        
        allMarkers.forEach(item => {
            let show = false;
            
            if (criteria === 'all') {
                show = true;
            } else if (criteria === 'ok') {
                // Montre les éoliennes OK et les gérants
                if (item.type === 'gerant' || (item.type === 'eolienne' && item.status === 'ok')) show = true;
            } else if (criteria === 'nok') {
                // Montre seulement les éoliennes en panne
                if (item.type === 'eolienne' && item.status === 'nok') show = true;
            }

            if(show) {
                item.marker.addTo(markersLayer);
            }
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>