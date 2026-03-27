<?php

use R301\Controleur\CommentaireControleur;
use R301\Controleur\JoueurControleur;
use R301\Component\Formulaire;



if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . '/joueur');
    exit;
    die();
}

$controleurJoueur = JoueurControleur::getInstance();
$joueur = $controleurJoueur->getJoueurById($_GET['id']);
?>

<h1>Commentaires de <?php echo htmlspecialchars($joueur['nom']); ?></h1>

<?php
$form = new Formulaire("commentaire/ajouter");
$form->addTextArea("contenu");
$form->addHiddenInput("joueurId", $_GET['id']);
$form->addButton("submit", "create", "Publier le commentaire", "Publier le commentaire");
echo $form;

$controleurCommentaire = CommentaireControleur::getInstance();
$commentaires = $controleurCommentaire->listerLesCommentairesDuJoueur($_GET['id']);

usort($commentaires, function ($a, $b) { return $b->getDate() <=> $a->getDate(); });

?>
<div class="container">
    <table>
        <tr>
            <th style="min-width: 100px; width: 1%">Date</th>
            <th style="width: 80%">Commentaire</th>
            <th style="width: 1%"></th>
        </tr>
        <?php foreach ($commentaires as $commentaire): ?>
        <form action="/joueur/commentaire/supprimer" method="post">
            <input type="hidden" name="commentaireId" value="<?php echo htmlspecialchars($commentaire['commentaire_id']); ?>"/>
            <input type="hidden" name="joueurId" value="<?php echo $_GET['id']; ?>" />
            <tr>
                <td><?php echo $commentaire["date"]; ?></td>
                <td><?php echo $commentaire["contenu"]; ?></td>
                <td class="actions">
                    <button class="delete" type="submit">Supprimer</button>
                </td>
            </tr>
        </form>
        <?php endforeach; ?>
    </table>
</div>
