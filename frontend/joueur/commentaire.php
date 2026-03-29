<?php

require_once __DIR__ . '/../Controleur/JoueurControleur.php';
require_once __DIR__ . '/../Controleur/CommentaireControleur.php';

use R301\Controleur\CommentaireControleur;
use R301\Controleur\JoueurControleur;
use R301\Component\Formulaire;

if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . '/joueur');
    exit;
}

$controleurJoueur = JoueurControleur::getInstance();
$joueur = $controleurJoueur->getJoueurById((int)$_GET['id']);

if ($joueur === null) {
    header('Location: ' . BASE_URL . '/joueur');
    exit;
}
?>

<h1>Commentaires de <?php echo htmlspecialchars($joueur['nom'] . ' ' . ($joueur['prenom'] ?? '')); ?></h1>

<?php
$form = new Formulaire(BASE_URL . "/joueur/commentaire/ajouter");
$form->addTextArea("contenu");
$form->addHiddenInput("joueurId", $_GET['id']);
$form->addButton("submit", "create", "Publier le commentaire", "Publier le commentaire");
echo $form;

$controleurCommentaire = CommentaireControleur::getInstance();
$commentaires = $controleurCommentaire->listerLesCommentairesDuJoueur((int)$_GET['id']);

usort($commentaires, function ($a, $b) { return $b['date'] <=> $a['date']; });
?>

<div class="container">
    <table>
        <tr>
            <th style="min-width: 100px; width: 1%">Date</th>
            <th style="width: 80%">Commentaire</th>
            <th style="width: 1%"></th>
        </tr>
        <?php foreach ($commentaires as $commentaire): ?>
        <form action="<?= BASE_URL ?>/joueur/commentaire/supprimer" method="post">
            <tr>
                <input type="hidden" name="commentaireId" value="<?= htmlspecialchars($commentaire['id'] ?? $commentaire['commentaireId'] ?? '') ?>" />
                <input type="hidden" name="joueurId" value="<?= htmlspecialchars($_GET['id']) ?>" />
                <td><?= htmlspecialchars($commentaire['date'] ?? '') ?></td>
                <td><?= htmlspecialchars($commentaire['contenu'] ?? '') ?></td>
                <td class="actions">
                    <button class="delete" type="submit"
                            onclick="return confirm('Supprimer ce commentaire ?')">
                        Supprimer
                    </button>
                </td>
            </tr>
        </form>
        <?php endforeach; ?>
    </table>
</div>