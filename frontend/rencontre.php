<?php

use R301\Controleur\RencontreControleur;
use R301\Component\SelectResultat;

$controleur = RencontreControleur::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST['action'])
        && isset($_POST['rencontreId'])
) {
    switch($_POST['action']) {
        case "ouvrirFeuilleDeMatch":
            header('Location: ' . BASE_URL . '/feuilleDeMatch/feuilleDeMatch?id='.$_POST['rencontreId']);
            die();
        case "ouvrirEvaluations":
            header('Location: ' . BASE_URL . '/feuilleDeMatch/evaluation?id='.$_POST['rencontreId']);
            die();
        case "modifier":
            header('Location: ' . BASE_URL . '/rencontre/modifier?id='.$_POST['rencontreId']);
            die();
        case "enregistrerResultat":
            if (isset($_POST['resultat'])) {
                if (!$controleur->enregistrerResultat($_POST['rencontreId'], $_POST['resultat'])) {
                    error_log("Erreur lors de la mise à jour du resultat");
                }
            }
            header('Location: ' . BASE_URL . '/rencontre');
            die();
        case "supprimer":
            if (!$controleur->supprimerRencontre($_POST['rencontreId'])) {
                error_log("Erreur lors de la suppression de la rencontre");
            }
            header('Location: ' . BASE_URL . '/rencontre');
            die();
    }
} else {

$rencontres = $controleur->listerToutesLesRencontres();

?>
<h1>Rencontres</h1>
<div class="overflow container">
    <table>
        <tr>
            <th style="width:10%">Date</th>
            <th style="width:10%">Equipe Adverse</th>
            <th style="width:20%">Adresse</th>
            <th style="width:8%">Lieu</th>
            <th style="width:8%">Résultat</th>
            <th style="width:20%; min-width: 200px;">Actions</th>
        </tr>
        <?php foreach ($rencontres as $rencontre):

            $resultatActuel = isset($rencontre['resultat']) ? $rencontre['resultat'] : null;
            $selectResultat = new SelectResultat(null, $resultatActuel);
            $dateMatch = new DateTime($rencontre['dateEtHeure']);
            $estPassee = $dateMatch < new DateTime();
        ?>
        <tr>
            <td><?php echo $dateMatch->format('d/m/Y H:i') ?></td>
            <td><?php echo htmlspecialchars($rencontre['equipeAdverse']) ?></td>
            <td><?php echo htmlspecialchars($rencontre['adresse']) ?></td>
            <td><?php echo htmlspecialchars($rencontre['lieu']) ?></td>

            <?php if ($estPassee && $resultatActuel === null): ?>
                <td>
                    <form action="<?php echo BASE_URL; ?>/rencontre" method="post">
                        <input type="hidden" name="rencontreId" value="<?php echo htmlspecialchars($rencontre['rencontreId']); ?>" />
                        <?php echo $selectResultat->toHTML(); ?>
                        <button class="create" name="action" value="enregistrerResultat">Enregistrer résultat</button>
                    </form>
                </td>
            <?php else: ?>
                <td><?php echo htmlspecialchars($resultatActuel !== null ? $resultatActuel : '') ?></td>
            <?php endif; ?>

            <td class="actions">
                <?php if (!$estPassee): ?>
                    <form action="<?php echo BASE_URL; ?>/rencontre" method="post" style="display:inline">
                        <input type="hidden" name="rencontreId" value="<?php echo htmlspecialchars($rencontre['rencontreId']); ?>" />
                        <button name="action" value="ouvrirFeuilleDeMatch" class="info">Feuilles de match</button>
                        <button name="action" value="modifier" class="update">Modifier</button>
                        <button name="action" value="supprimer" class="delete">Supprimer</button>
                    </form>
                <?php else: ?>
                    <form action="<?php echo BASE_URL; ?>/rencontre" method="post" style="display:inline">
                        <input type="hidden" name="rencontreId" value="<?php echo htmlspecialchars($rencontre['rencontreId']); ?>" />
                        <button name="action" value="ouvrirEvaluations" class="info">Évaluations</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php } ?>