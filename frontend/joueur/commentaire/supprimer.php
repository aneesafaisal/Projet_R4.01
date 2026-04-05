<?php

require_once __DIR__ . '/../../Controleur/CommentaireControleur.php';

use R301\Controleur\CommentaireControleur;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['commentaireId'])) {
        $controleurCommentaire = CommentaireControleur::getInstance();
        if (!$controleurCommentaire->supprimerCommentaire((int) $_POST['commentaireId'])) {
            error_log("Erreur lors de la suppression du commentaire");
        }
    }
}

if (isset($_POST['joueurId'])) {
    header('Location: ' . BASE_URL . '/joueur/commentaire?id=' . $_POST['joueurId']);
} else {
    header('Location: ' . BASE_URL . '/joueur');
}
exit;