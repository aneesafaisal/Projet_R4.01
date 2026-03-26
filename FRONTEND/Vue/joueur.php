<?php
// Force le chargement de la version FRONTEND avant que l'autoloader
// ne charge la version backend
require_once __DIR__ . '/../Controleur/JoueurControleur.php';

use R301\Controleur\JoueurControleur;

$controleur = JoueurControleur::getInstance();

if (isset($_GET['recherche']) || isset($_GET['statut'])) {
    $joueurs = $controleur->rechercherLesJoueurs(
        $_GET['recherche'] ?? '',
        $_GET['statut'] ?? ''
    );
} else {
    $joueurs = $controleur->listerTousLesJoueurs();
}
?>

<h1>Joueurs</h1>
<div class="container">
    <form action="joueur" method="get">
        <div class="row">
            <div class="invCol-80">
                <input type="search" name="recherche" placeholder="Rechercher"
                    value="<?= htmlspecialchars($_GET['recherche'] ?? '') ?>"/>
            </div>
        </div>
        <div class="row">
            <div class="invCol-80">
                <select name="statut" id="statut">
                    <option value="">Tous</option>
                    <option value="ACTIF"    <?= (($_GET['statut'] ?? '') === 'ACTIF')    ? 'selected' : '' ?>>Actif</option>
                    <option value="BLESSE"   <?= (($_GET['statut'] ?? '') === 'BLESSE')   ? 'selected' : '' ?>>Blessé</option>
                    <option value="ABSENT"   <?= (($_GET['statut'] ?? '') === 'ABSENT')   ? 'selected' : '' ?>>Absent</option>
                    <option value="SUSPENDU" <?= (($_GET['statut'] ?? '') === 'SUSPENDU') ? 'selected' : '' ?>>Suspendu</option>
                </select>
            </div>
            <div class="invCol-20">
                <input class="filter-button" type="submit" value="Filtrer">
            </div>
        </div>
    </form>
</div>

<div class="overflow container">
    <table style="width: 100%">
        <tr>
            <th style="width:8%">Numero Licence</th>
            <th style="width:12%">Nom</th>
            <th style="width:12%">Prenom</th>
            <th style="width:12%">Date de naissance</th>
            <th style="width:12%">Taille</th>
            <th style="width:12%">Poids</th>
            <th style="width:12%">Statut</th>
            <th style="width:20%; min-width: 370px;">Actions</th>
        </tr>

        <?php foreach ($joueurs as $joueur): ?>
            <tr>
                <td><?= htmlspecialchars($joueur['numeroDeLicence']) ?></td>
                <td><?= htmlspecialchars($joueur['nom']) ?></td>
                <td><?= htmlspecialchars($joueur['prenom']) ?></td>
                <td><?= date('d/m/Y', strtotime($joueur['dateDeNaissance'])) ?></td>
                <td><?= $joueur['tailleEnCm'] ?> cm</td>
                <td><?= $joueur['poidsEnKg'] ?> kg</td>
                <td><?= htmlspecialchars($joueur['statut']) ?></td>
                <td class="actions">
                    <form action="joueur/modifier" method="get">
                        <button class="update" type="submit" name="id"
                            value="<?= $joueur['joueurId'] ?>">Modifier</button>
                    </form>
                    <form action="joueur/supprimer" method="post">
                        <button class="delete" type="submit" name="id"
                            value="<?= $joueur['joueurId'] ?>"
                            onclick="return confirm('Voulez-vous vraiment supprimer ce joueur?')">
                            Supprimer
                        </button>
                    </form>
                    <form action="joueur/commentaire" method="get">
                        <button class="info" type="submit" name="id"
                            value="<?= $joueur['joueurId'] ?>">Commentaires</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <p><?= count($joueurs) ?> joueur(s) retourné(s)</p>
</div>