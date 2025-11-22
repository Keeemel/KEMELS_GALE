<?php
session_start();
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/bdd.php';
require_once __DIR__ . '/../includes/fonctions.php';
require_once __DIR__ . '/../includes/authentification.php';

// reste du code...


if (!is_logged()) redirect('../connexion.php');

$uid = $_SESSION['user_id'];
$errors = [];

// Récupérer les sites pour le select
$sites = $pdo->query("SELECT id, nom FROM site ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!csrf_verify($_POST['csrf'] ?? '')) {
        $errors[] = "Session expirée. Réessayez.";
    } else {
        $site_id = $_POST['site_id'] ?: null;
        $identifiant = trim($_POST['identifiant'] ?? '');
        $latitude = floatval($_POST['latitude'] ?? 0);
        $longitude = floatval($_POST['longitude'] ?? 0);
        $capacite_kw = floatval($_POST['capacite_kw'] ?? 0);
        $etat = $_POST['etat'] ?? 'OK';
        $energie_t_kw = floatval($_POST['energie_t_kw'] ?? 0);

        if ($identifiant === '') $errors[] = "L'identifiant est obligatoire.";
        
        // Validation de l'état
        $etats_valides = ['OK', 'STOP', 'ALERTE', 'MAINT'];
        if (!in_array($etat, $etats_valides, true)) {
            $errors[] = "État invalide.";
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO eolienne (site_id, identifiant, latitude, longitude, capacite_kw, etat, energie_t_kw, gerant_id)
                    VALUES (:site_id, :identifiant, :lat, :lng, :cap, :etat, :energie, :uid)
                ");
                $stmt->execute([
                    ':site_id'=>$site_id,
                    ':identifiant'=>$identifiant,
                    ':lat'=>$latitude,
                    ':lng'=>$longitude,
                    ':cap'=>$capacite_kw,
                    ':etat'=>$etat,
                    ':energie'=>$energie_t_kw,
                    ':uid'=>$uid
                ]);
                flash('success', 'Éolienne ajoutée avec succès.');
                header('Location: mes_eoliennes.php');
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $errors[] = "Cet identifiant existe déjà.";
                } else {
                    $errors[] = "Erreur lors de l'ajout.";
                }
            }
        }
    }
}
?>

<main>
<h1>Ajouter une éolienne</h1>

<?php if(!empty($errors)): ?>
<ul>
<?php foreach($errors as $e): ?>
<li><?= e($e) ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    
    <label>Site :
        <select name="site_id">
            <option value="">-- Aucun --</option>
            <?php foreach($sites as $s): ?>
                <option value="<?= $s['id'] ?>"><?= e($s['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>

    <label>Identifiant : <input type="text" name="identifiant" required></label><br>
    <label>Latitude : <input type="number" step="0.000001" name="latitude"></label><br>
    <label>Longitude : <input type="number" step="0.000001" name="longitude"></label><br>
    <label>Capacité (kW) : <input type="number" step="0.1" name="capacite_kw"></label><br>
    <label>État :
        <select name="etat">
            <option value="OK">OK</option>
            <option value="STOP">STOP</option>
            <option value="ALERTE">ALERTE</option>
            <option value="MAINT">MAINT</option>
        </select>
    </label><br>
    <label>Énergie (t kW) : <input type="number" step="0.1" name="energie_t_kw"></label><br>

    <button type="submit">Ajouter</button>
</form>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
