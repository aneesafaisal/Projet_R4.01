<?php

use R301\Controleur\CommentaireControleur;

if (!defined('BASE_URL')) {
    define('BASE_URL', '/Projet_R4.01');
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['joueurId'])
    && isset($_POST['contenu'])
) {
    $controleur = CommentaireControleur::getInstance();

    if (
        !$controleur->ajouterCommentaire(
            $_POST['contenu'],
            $_POST['joueurId']
        )
    ) {
        error_log("Erreur lors de la création du commentaire");
    }
}

if (isset($_POST['joueurId'])) {
    header('Location: ' . BASE_URL . '/joueur/commentaire?id=' . $_POST['joueurId']);
    exit;
} else {
    header('Location: ' . BASE_URL . '/joueur');
    exit;
}