<?php

require_once __DIR__ . '/../Controleur/ParticipationControleur.php';

use R301\Controleur\ParticipationControleur;
use R301\Component\SelectPerformance;

$controleur = ParticipationControleur::getInstance();

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
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
    <div
        style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; padding-right: 30px">
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
                    <th style="width:45%">Mettre à jour la performance</th>
                </tr>

                <?php foreach ($postes as $poste):
                    $participant = null;
                    foreach ($feuilleDeMatch as $p) {
                        if (
                            isset($p['poste']) && $p['poste'] === $poste &&
                            isset($p['titulaire_ou_remplacant']) && $p['titulaire_ou_remplacant'] === $type
                        ) {
                            $participant = $p;
                            break;
                        }
                    }

                    $selectedValue = isset($participant['performance']) ? $participant['performance'] : null;
                    $select = new SelectPerformance(null, $selectedValue);

                    $joueurString = '';
                    if ($participant !== null && isset($participant['joueur'])) {
                        $nom = isset($participant['joueur']['nom']) ? $participant['joueur']['nom'] : '';
                        $prenom = isset($participant['joueur']['prenom']) ? $participant['joueur']['prenom'] : '';
                        $joueurString = trim($nom . ' ' . $prenom);
                    }

                    $performanceString = isset($participant['performance']) ? $participant['performance'] : '';

                    if (isset($participant['rencontreId'])) {
                        $rencontreId = $participant['rencontreId'];
                    } elseif (isset($participant['rencontre']['rencontreId'])) {
                        $rencontreId = $participant['rencontre']['rencontreId'];
                    } else {
                        $rencontreId = isset($_GET['id']) ? $_GET['id'] : '';
                    }

                    if (isset($participant['participationId'])) {
                        $participationId = $participant['participationId'];
                    } elseif (isset($participant['id'])) {
                        $participationId = $participant['id'];
                    } else {
                        $participationId = '';
                    }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($poste) ?></td>
                        <td><?= htmlspecialchars($joueurString) ?></td>
                        <td><?= htmlspecialchars($performanceString) ?></td>
                        <td colspan="2">
                            <?php if ($participant !== null): ?>
                                <form action="<?= BASE_URL ?>/feuilleDeMatch/evaluation" method="post"
                                    style="display: flex; align-items: center; gap: 8px;">
                                    <input type="hidden" name="rencontreId" value="<?= htmlspecialchars($rencontreId) ?>" />
                                    <input type="hidden" name="participationId" value="<?= htmlspecialchars($participationId) ?>" />
                                    <?= $select->toHTML() ?>
                                    <button class="update" type="submit" name="action" value="update">Mettre à jour</button>
                                    <button class="delete" type="submit" name="action" value="delete">Supprimer</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endforeach; ?>
    </div>
<?php } ?>