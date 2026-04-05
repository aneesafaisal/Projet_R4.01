<h1>Modifier une rencontre</h1>
<?php

use R301\Controleur\RencontreControleur;
use R301\Component\Formulaire;

$controleur = RencontreControleur::getInstance();

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_GET['id'])
    && isset($_POST['dateHeure'])
    && isset($_POST['equipeAdverse'])
    && isset($_POST['adresse'])
    && isset($_POST['lieu'])
) {
    if (
        $controleur->modifierRencontre(
            (int) $_GET['id'],
            $_POST['dateHeure'],
            $_POST['equipeAdverse'],
            $_POST['adresse'],
            $_POST['lieu']
        )
    ) {
        header('Location: ' . BASE_URL . '/rencontre');
        exit;
    } else {
        error_log("Erreur lors de la modification de la rencontre");
        exit;
    }
} else {
    if (!isset($_GET['id'])) {
        header('Location: ' . BASE_URL . '/rencontre');
        exit;
    }

    $rencontre = $controleur->getRencontreById((int) $_GET['id']);

    if ($rencontre === null) {
        header('Location: ' . BASE_URL . '/rencontre');
        exit;
    }

    $formulaire = new Formulaire("modifier?id=" . $_GET['id']);

    $dateValue = (new DateTime($rencontre['dateEtHeure']))->format('Y-m-d\TH:i');
    $now = date('Y-m-d\TH:i');

    $formulaire->setDateTime("Date", "dateHeure", $now, $dateValue);
    $formulaire->setText("Equipe adverse", "equipeAdverse", "", $rencontre['equipeAdverse']);
    $formulaire->setText("Adresse", "adresse", "", $rencontre['adresse']);
    $formulaire->setSelect("Lieu", ['DOMICILE', 'EXTERIEUR'], "lieu", $rencontre['lieu']);

    $formulaire->addButton("submit", "update", "modifier", "Modifier");
    echo $formulaire;
}