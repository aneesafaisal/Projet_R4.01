<?php

require_once __DIR__ . '/../Controleur/ParticipationControleur.php';
require_once __DIR__ . '/../Controleur/JoueurControleur.php';

use R301\Controleur\ParticipationControleur;
use R301\Controleur\JoueurControleur;
use R301\Component\Select;

$controleur = ParticipationControleur::getInstance();
$joueurControleur = JoueurControleur::getInstance();

if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . '/rencontre');
    die();
}

$feuilleDeMatch = $controleur->getFeuilleDeMatch($_GET['id']);
$joueursSelectionnables = $joueurControleur->listerLesJoueursSelectionnablesPourUnMatch($_GET['id']);
?>
<div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; padding-right: 30px">
    <h1>Feuille de Match</h1>
    <?php 
    $estComplete = true;
    foreach ($feuilleDeMatch as $p) {
        if (empty($p['joueur'])) {
            $estComplete = false;
            break;
        }
    }
    ?>
    <?php if ($estComplete): ?>
        <div class="etat-feuille-de-match feuille-de-match-complete">COMPLÈTE</div>
    <?php else: ?>
        <div class="etat-feuille-de-match feuille-de-match-incomplete">INCOMPLÈTE</div>
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
            <th style="width:30%">Joueur</th>
            <th style="width:35%">Sélectionner un joueur</th>
            <th style="width:20%; min-width: 150px;"></th>
        </tr>

        <?php foreach ($postes as $poste):
            $participant = null;
            foreach ($feuilleDeMatch as $p) {
                if (($p['poste'] ?? '') === $poste && ($p['titulaire_ou_remplacant'] ?? '') === $type) {
                    $participant = $p;
                    break;
                }
            }
            $selectableValues = [];
            foreach ($joueursSelectionnables as $j) {
                $selectableValues[$j['joueurId']] = trim(($j['nom'] ?? '') . ' ' . ($j['prenom'] ?? ''));
            }
            if ($participant !== null && isset($participant['joueur'])) {
                $idActuel = $participant['joueur']['joueurId'] ?? $participant['joueurId'] ?? null;
                if ($idActuel) {
                    $selectableValues[$idActuel] = trim(($participant['joueur']['nom'] ?? '') . ' ' . ($participant['joueur']['prenom'] ?? ''));
                }
            }

            $selectedValue = $participant !== null && isset($participant['joueur'])
                ? trim(($participant['joueur']['nom'] ?? '') . ' ' . ($participant['joueur']['prenom'] ?? ''))
                : null;

            $select = new Select($selectableValues, "joueurId", null, $selectedValue);
        ?>
        <form action="<?= BASE_URL ?>/feuilleDeMatch/modifier" method="post">
            <tr>
                <input type="hidden" name="participationId" value="<?= $participant['id'] ?? $participant['participationId'] ?? '' ?>" />
                <input type="hidden" name="poste" value="<?= htmlspecialchars($poste) ?>" />
                <input type="hidden" name="rencontreId" value="<?= htmlspecialchars($_GET['id']) ?>" />
                <input type="hidden" name="titulaireOuRemplacant" value="<?= htmlspecialchars($type) ?>" />

                <td><?= htmlspecialchars($poste) ?></td>
                <td><?= $participant !== null && isset($participant['joueur'])
                    ? htmlspecialchars(trim(($participant['joueur']['nom'] ?? '') . ' ' . ($participant['joueur']['prenom'] ?? '')))
                    : '' ?>
                </td>
                <td><?= $select->toHTML() ?></td>
                <td class="actions">
                    <?php if ($participant !== null): ?>
                        <button class="update" type="submit" name="action" value="update">Modifier</button>
                        <button class="delete" type="submit" name="action" value="delete" style="margin-left: 8px">Supprimer</button>
                    <?php else: ?>
                        <button class="create" type="submit" name="action" value="create">Assigner</button>
                    <?php endif; ?>
                </td>
            </tr>
        </form>
        <?php endforeach; ?>
    </table>
    <?php endforeach; ?>
</div>