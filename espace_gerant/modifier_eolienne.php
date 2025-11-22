<?php
session_start();
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/bdd.php';
require_once __DIR__ . '/../includes/fonctions.php';
require_once __DIR__ . '/../includes/authentification.php';

// reste du code...


if (!is_logged()) redirect('../connexion.php');

$uid = $_SESSION['user_id'];
$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM eolienne WHERE id=:id AND gerant_id=:uid");
$stmt->execute([':id'=>$id, ':uid'=>$uid]);
$eolienne = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$eolienne) die("Éolienne introuvable.");

$sites = $pdo->query("SELECT id, nom FROM site ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!csrf_verify($_POST['csrf'] ?? '')) {
        $errors[] = "Session expirée. Réessaie.";
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
                    UPDATE eolienne 
                    SET site_id=:site_id, identifiant=:identifiant, latitude=:lat, longitude=:lng, capacite_kw=:cap, etat=:etat, energie_t_kw=:energie
                    WHERE id=:id AND gerant_id=:uid
                ");
                $stmt->execute([
                    ':site_id'=>$site_id,
                    ':identifiant'=>$identifiant,
                    ':lat'=>$latitude,
                    ':lng'=>$longitude,
                    ':cap'=>$capacite_kw,
                    ':etat'=>$etat,
                    ':energie'=>$energie_t_kw,
                    ':id'=>$id,
                    ':uid'=>$uid
                ]);
                flash('success', 'Éolienne modifiée avec succès.');
                header('Location: mes_eoliennes.php');
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errors[] = "Cet identifiant existe déjà.";
                } else {
                    $errors[] = "Erreur lors de la modification.";
                }
            }
        }
    }
}
?>

<main>
<h1>Modifier l'éolienne</h1>

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
                <option value="<?= $s['id'] ?>" <?= ($eolienne['site_id']==$s['id']?'selected':'') ?>><?= e($s['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>

    <label>Identifiant : <input type="text" name="identifiant" value="<?= e($eolienne['identifiant']) ?>" required></label><br>
    <label>Latitude : <input type="number" step="0.000001" name="latitude" value="<?= e($eolienne['latitude']) ?>"></label><br>
    <label>Longitude : <input type="number" step="0.000001" name="longitude" value="<?= e($eolienne['longitude']) ?>"></label><br>
    <label>Capacité (kW) : <input type="number" step="0.1" name="capacite_kw" value="<?= e($eolienne['capacite_kw']) ?>"></label><br>
    <label>État :
        <select name="etat">
            <option value="OK" <?= ($eolienne['etat']=='OK'?'selected':'') ?>>OK</option>
            <option value="STOP" <?= ($eolienne['etat']=='STOP'?'selected':'') ?>>STOP</option>
            <option value="ALERTE" <?= ($eolienne['etat']=='ALERTE'?'selected':'') ?>>ALERTE</option>
            <option value="MAINT" <?= ($eolienne['etat']=='MAINT'?'selected':'') ?>>MAINT</option>
        </select>
    </label><br>
    <label>Énergie (t kW) : <input type="number" step="0.1" name="energie_t_kw" value="<?= e($eolienne['energie_t_kw']) ?>"></label><br>

    <button type="submit">Modifier</button>
</form>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
