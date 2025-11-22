<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/bdd.php';
require_once __DIR__ . '/includes/fonctions.php';
require_once __DIR__ . '/includes/authentification.php';

// Récupération des gérants
$gerants = [];
try {
    $stmt = $pdo->query("
        SELECT u.id, u.prenom, u.nom, gp.photo_url, gp.latitude, gp.longitude
        FROM user u
        LEFT JOIN gerant_profil gp ON u.id = gp.user_id
        WHERE u.role IN ('gerant','admin')
    ");
    $gerants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $gerants = [];
}

// Récupération des éoliennes
$eoliennes = [];
try {
    $stmt = $pdo->query("
        SELECT e.id, e.identifiant, e.latitude, e.longitude, e.capacite_kw, e.etat,
               u.prenom, u.nom, gp.photo_url
        FROM eolienne e
        LEFT JOIN user u ON e.gerant_id = u.id
        LEFT JOIN gerant_profil gp ON u.id = gp.user_id
    ");
    $eoliennes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $eoliennes = [];
}
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<main class="page-carte">
    <h1>Carte du parc éolien</h1>
    <p class="muted">Visualisation des gérants et des éoliennes avec leur état.</p>

    <div id="map" style="height:600px;"></div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const map = L.map('map').setView([46.5, 2], 6);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            const gerants = <?= json_encode($gerants, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            const eoliennes = <?= json_encode($eoliennes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            const bounds = [];

            // Marqueurs gérants
            gerants.forEach(g => {
                if (g.latitude && g.longitude) {
                    const lat = parseFloat(g.latitude);
                    const lng = parseFloat(g.longitude);
                    const photo = g.photo_url ? g.photo_url : 'assets/images/gerants/default.jpg';

                    const marker = L.marker([lat, lng]).addTo(map);
                    marker.bindPopup(`
                        <div class="popup-gerant">
                            <img src="${photo}" alt="${g.prenom} ${g.nom}" style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin-bottom:6px;">
                            <h3>${g.prenom} ${g.nom}</h3>
                            <p>Gérant</p>
                        </div>
                    `);
                    bounds.push([lat, lng]);
                }
            });

            // Marqueurs éoliennes avec icône personnalisée
            const eolienneIcon = L.icon({
                iconUrl: 'assets/images/eoliennes/iconEolienne.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            });

            eoliennes.forEach(e => {
                if (e.latitude && e.longitude) {
                    const lat = parseFloat(e.latitude);
                    const lng = parseFloat(e.longitude);

                    const marker = L.marker([lat, lng], { icon: eolienneIcon }).addTo(map);

                    const photo = e.photo_url ? e.photo_url : 'assets/images/gerants/default.jpg';

                    marker.bindPopup(`
                        <div class="popup-eolienne" style="text-align:center;">
                            <img src="${photo}" alt="${e.prenom || ''} ${e.nom || ''}" 
                                 style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin-bottom:6px;">
                            <h3>${e.identifiant}</h3>
                            <p>Gérant: ${e.prenom || 'N/A'} ${e.nom || ''}</p>
                            <p>Capacité: ${e.capacite_kw} kW</p>
                            <p>État: <strong>${e.etat}</strong></p>
                        </div>
                    `);

                    bounds.push([lat, lng]);
                }
            });

            if (bounds.length > 0) {
                map.fitBounds(bounds, {padding: [50, 50]});
            }
        });
    </script>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
