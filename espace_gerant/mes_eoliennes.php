<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../base_donnees/bdd.php';
require_once __DIR__ . '/../includes/fonctions.php';
require_once __DIR__ . '/../includes/authentification.php';


if (!is_logged()) redirect('login.php');

$uid = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT e.*, s.nom AS site_nom 
        FROM eolienne e
        LEFT JOIN site s ON e.site_id = s.id
        WHERE e.gerant_id = :uid
        ORDER BY e.id DESC
    ");
    $stmt->execute([':uid' => $uid]);
    $eoliennes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $eoliennes = [];
}
?>

<main>
<h1>Mes éoliennes</h1>
<a href="ajouter_eolienne.php" class="btn">Ajouter une éolienne</a>

<?php if(empty($eoliennes)): ?>
    <p>Aucune éolienne enregistrée.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>Identifiant</th>
            <th>Site</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Capacité (kW)</th>
            <th>État</th>
            <th>Énergie (t kW)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($eoliennes as $e): ?>
        <tr>
            <td><?= e($e['identifiant']) ?></td>
            <td><?= e($e['site_nom']) ?></td>
            <td><?= e($e['latitude']) ?></td>
            <td><?= e($e['longitude']) ?></td>
            <td><?= e($e['capacite_kw']) ?></td>
            <td><?= e($e['etat']) ?></td>
            <td><?= e($e['energie_t_kw']) ?></td>
            <td>
                <a href="modifier_eolienne.php?id=<?= $e['id'] ?>">Modifier</a> |
                <a href="supprimer_eolienne.php?id=<?= $e['id'] ?>" onclick="return confirm('Supprimer cette éolienne ?')">Supprimer</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
