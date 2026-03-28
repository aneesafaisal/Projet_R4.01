<?php

require_once __DIR__ . '/../Controleur/ParticipationControleur.php';

use R301\Controleur\ParticipationControleur;
use R301\Component\SelectPerformance;

$controleur = ParticipationControleur::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST['action'])
        && isset($_POST['participationId'])
        && isset($_POST['rencontreId'])
) {
    switch ($_POST['action']) {
        case "update":
            if (isset($_POST['performance'])) {
                if (!$controleur->mettreAJourLaPerformance($_POST['participationId'], $_POST['performance'])) {
                    error_log("Erreur lors de la mise à jour de la performance");
                }
            }
            break;
        case "delete":
            if (!$controleur->supprimerLaPerformance($_POST['participationId'])) {
                error_log("Erreur lors de la suppression de la performance");
            }
            break;
    }

    header('Location: ' . BASE_URL . '/feuilleDeMatch/evaluation?id=' . $_POST['rencontreId']);
    die();
} else {
    if (!isset($_GET['id'])) {
        header('Location: ' . BASE_URL . '/rencontre');
        die();
    }

    $feuilleDeMatch = $controleur->getFeuilleDeMatch($_GET['id']);

    // Calcul estEvalue (remplace la méthode du modèle)
    $estEvalue = true;
    if (!empty($feuilleDeMatch)) {
        foreach ($feuilleDeMatch as $p) {
            if (empty($p['performance'])) {
                $estEvalue = false;
                break;
            }
        }
    } else {
        $estEvalue = false;
    }
?>
<div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; padding-right: 30px">
    <h1>Évaluations</h1>
    <?php if ($estEvalue): ?>
        <div class="etat-feuille-de-match feuille-de-match-complete">TERMINÉES</div>
    <?php else: ?>
        <div class="etat-feuille-de-match feuille-de-match-incomplete">INCOMPLÈTES</div>
    <?php endif; ?>
</div>

<div class="container" style="display: flex; flex-direction: row; justify-content: space-between">
    <?php
    $types = ['TITULAIRE', 'REMPLACANT'];
    $postes = ['TOPLANE', 'JUNGLE', 'MIDLANE', 'ADCARRY', 'SUPPORT'];

    foreach ($types as $type):
    ?>
        <table style="width: 49.5%">
            <caption><?= htmlspecialchars($type) ?>S</caption>
            <tr>
                <th style="width:15%">Poste</th>
                <th style="width:25%">Joueur</th>
                <th style="width:15%">Performance</th>
                <th style="width:20%">Mettre à jour la performance</th>
                <th style="width:25%; min-width: 150px;"></th>
            </tr>

            <?php foreach ($postes as $poste):
                // Recherche du participant correspondant
                $participant = null;
                foreach ($feuilleDeMatch as $p) {
                    if (isset($p['poste']) && $p['poste'] === $poste &&
                        isset($p['titulaire_ou_remplacant']) && $p['titulaire_ou_remplacant'] === $type) {
                        $participant = $p;
                        break;
                    }
                }

                $selectedValue = $participant['performance'] ?? null;
                $select = new SelectPerformance(null, $selectedValue);

                $joueurString = '';
                if ($participant !== null && isset($participant['joueur'])) {
                    $joueurString = trim(($participant['joueur']['nom'] ?? '') . ' ' . ($participant['joueur']['prenom'] ?? ''));
                }

                $performanceString = $participant['performance'] ?? '';
                $rencontreId = $participant['rencontreId'] ?? ($participant['rencontre']['rencontreId'] ?? $_GET['id'] ?? '');
                $participationId = $participant['participationId'] ?? ($participant['id'] ?? '');
            ?>
            <form action="<?= BASE_URL ?>/feuilleDeMatch/evaluation" method="post">
                <tr>
                    <input type="hidden" name="rencontreId" value="<?= htmlspecialchars($rencontreId) ?>" />
                    <input type="hidden" name="participationId" value="<?= htmlspecialchars($participationId) ?>" />
                    <td><?= htmlspecialchars($poste) ?></td>
                    <td><?= htmlspecialchars($joueurString) ?></td>
                    <td><?= htmlspecialchars($performanceString) ?></td>
                    <td><?= $select->toHTML() ?></td>
                    <?php if ($participant !== null): ?>
                    <td class="actions">
                        <button class="update" type="submit" name="action" value="update">Mettre à jour</button>
                        <button class="delete" type="submit" name="action" value="delete" style="margin-left: 8px">Supprimer</button>
                    </td>
                    <?php else: ?>
                    <td></td>
                    <?php endif; ?>
                </tr>
            </form>
            <?php endforeach; ?>
        </table>
    <?php endforeach; ?>
</div>
<?php } ?>