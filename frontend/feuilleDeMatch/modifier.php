<?php

require_once __DIR__ . '/../Controleur/ParticipationControleur.php';

use R301\Controleur\ParticipationControleur;

$controleur = ParticipationControleur::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && isset($_POST['poste'])
    && isset($_POST['titulaireOuRemplacant'])
    && isset($_POST['rencontreId'])
) {
    $rencontreId = (int)$_POST['rencontreId'];

    switch ($_POST['action']) {
        case "create":
            if (isset($_POST['joueurId']) && $_POST['joueurId'] !== "") {
                if (!$controleur->assignerUnParticipant(
                    (int)$_POST['joueurId'],
                    $rencontreId,
                    $_POST['poste'],
                    $_POST['titulaireOuRemplacant'] 
                )) {
                    error_log("Erreur lors de l'ajout d'une participation");
                }
            }
            break;

        case "update":
            if (isset($_POST['participationId']) && isset($_POST['joueurId']) && $_POST['joueurId'] !== "") {
                if (!$controleur->modifierParticipation(
                    (int)$_POST['participationId'],
                    $_POST['poste'],
                    $_POST['titulaireOuRemplacant'],
                    (int)$_POST['joueurId']
                )) {
                    error_log("Erreur lors de la modification de la participation");
                }
            }
            break;

        case "delete":
            if (isset($_POST['participationId'])) {
                if (!$controleur->supprimerLaParticipation((int)$_POST['participationId'])) {
                    error_log("Erreur lors de la suppression de la participation");
                }
            }
            break;
    }

    header('Location: ' . BASE_URL . '/feuilleDeMatch/feuilleDeMatch?id=' . $rencontreId);
    die();
} else {
    if (isset($_POST['rencontreId']) || isset($_GET['id'])) {
        $id = $_POST['rencontreId'] ?? $_GET['id'] ?? 0;
        header('Location: ' . BASE_URL . '/feuilleDeMatch/feuilleDeMatch?id=' . $id);
    } else {
        header('Location: ' . BASE_URL . '/rencontre');
    }
    die();
}