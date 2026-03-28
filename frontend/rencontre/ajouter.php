<h1>Ajouter une rencontre</h1>
<?php

use R301\Controleur\RencontreControleur;
use R301\Component\Formulaire;

if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST['dateHeure'])
        && isset($_POST['equipeAdverse'])
        && isset($_POST['adresse'])
        && isset($_POST['lieu'])
) {
    $controleur = RencontreControleur::getInstance();

    if (
        $controleur->ajouterRencontre(
            $_POST['dateHeure'],    
            $_POST['equipeAdverse'],
            $_POST['adresse'],
            $_POST['lieu']       
        )
    ) {
        header('Location: ' . BASE_URL . '/rencontre');
        exit;
    } else {
        error_log("Erreur lors de la création de la rencontre");
    }
} else {
    $formulaire = new Formulaire("ajouter");
    
    $now = date('Y-m-d\TH:i');
    $formulaire->setDateTime("Date", "dateHeure", $now, $now);
    $formulaire->setText("Equipe adverse", "equipeAdverse");
    $formulaire->setText("Adresse", "adresse");
    $formulaire->setSelect("Lieu", ['DOMICILE', 'EXTERIEUR'], "lieu");
    
    $formulaire->addButton("Submit", "create", "Valider", "Valider");
    echo $formulaire;
}