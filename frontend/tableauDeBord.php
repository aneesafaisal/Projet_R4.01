<?php

use R301\Controleur\StatistiquesControleur;

$controleur = StatistiquesControleur::getInstance();
$statistiquesEquipe = $controleur->getStatistiquesEquipe();
$statistiquesJoueurs = $controleur->getStatistiquesJoueurs();

?>

<div class="TripleGrid">
    <div>
        <h1><?= $statistiquesEquipe['nbVictoires'] ?? 0 ?></h1>
        <p> matchs gagnés</p>
    </div>
    <div>
        <h1><?= $statistiquesEquipe['nbNuls'] ?? 0 ?></h1>
        <p> matchs nuls</p>
    </div>
    <div>
        <h1><?= $statistiquesEquipe['nbDefaites'] ?? 0 ?></h1>
        <p> matchs perdus</p>
    </div>
    <div>
        <h1><?= $statistiquesEquipe['pourcentageDeVictoires'] ?? 0 ?>%</h1>
        <p> de matchs gagnés</p>
    </div>
    <div>
        <h1><?= $statistiquesEquipe['pourcentageDeNuls'] ?? 0 ?>%</h1>
        <p> de matchs nuls</p>
    </div>
    <div>
        <h1><?= $statistiquesEquipe['pourcentageDeDefaites'] ?? 0 ?>%</h1>
        <p> de matchs perdus</p>
    </div>
</div>

<div class="overflow">
    <table>
        <tr>
            <th style="width:15%;">Joueur</th>
            <th style="width:7%;">Statut</th>
            <th style="width:7%;">Poste le plus performant</th>
            <th style="width:7%;">Nombre de matchs consécutifs</th>
            <th style="width:7%;">Nombre titularisations</th>
            <th style="width:7%;">Nombre remplaçants</th>
            <th style="width:7%;">Moyenne évaluations</th>
            <th style="width:7%;">Pourcentage gagnés</th>
        </tr>
        <?php foreach ($statistiquesJoueurs as $stat): ?>
            <tr>
                <td><?= htmlspecialchars($stat['joueur']['nom'] ?? '') . ' ' . htmlspecialchars($stat['joueur']['prenom'] ?? '') ?>
                </td>
                <td><?= htmlspecialchars($stat['joueur']['statut'] ?? '') ?></td>
                <td><?= htmlspecialchars($stat['posteLePlusPerformant'] ?? '') ?></td>
                <td><?= $stat['nbRencontresConsecutivesADate'] ?? 0 ?></td>
                <td><?= $stat['nbTitularisations'] ?? 0 ?></td>
                <td><?= $stat['nbRemplacant'] ?? 0 ?></td>
                <td><?= $stat['moyenneDesEvaluations'] ?? 0 ?></td>
                <td><?= $stat['pourcentageDeMatchsGagnes'] ?? 0 ?>%</td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>