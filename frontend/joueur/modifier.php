<h1>Modifier un joueur</h1>
<?php

require_once __DIR__ . '/../Controleur/JoueurControleur.php';

use R301\Controleur\JoueurControleur;
use R301\Component\Formulaire;

$controleur = JoueurControleur::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_GET['id'])
    && isset($_POST['nom'])
    && isset($_POST['prenom'])
    && isset($_POST['dateDeNaissance'])
    && isset($_POST['tailleEnCm'])
    && isset($_POST['poidsEnKg'])
    && isset($_POST['statut'])
) {
    if (
        $controleur->modifierJoueur(
            (int)$_GET['id'],
            $_POST['nom'],
            $_POST['prenom'],
            $_POST['numeroDeLicence'],
            $_POST['dateDeNaissance'],
            (int)$_POST['tailleEnCm'],
            (int)$_POST['poidsEnKg'],
            $_POST['statut']
        )
    ) {
        header('Location: ' . BASE_URL . '/joueur');
        exit;
    } else {
        error_log("Erreur lors de la modification du joueur");
    }

} else {

    if (!isset($_GET['id'])) {
        header('Location: ' . BASE_URL . '/joueur');
        exit;
    }

    $joueur = $controleur->getJoueurById((int)$_GET['id']);

    if ($joueur === null) {
        header('Location: ' . BASE_URL . '/joueur');
        exit;
    }

    $formulaire = new Formulaire("modifier?id=" . $_GET['id']);
    $formulaire->setText("Nom", "nom", "", $joueur['nom']);
    $formulaire->setText("Prenom", "prenom", "", $joueur['prenom']);
    $formulaire->setText("Numéro de license", "numeroDeLicence", "00042", $joueur['numeroDeLicence']);
    $formulaire->setDate("Date de naissance", "dateDeNaissance", $joueur['dateDeNaissance']);
    $formulaire->setText("Taille (en cm)", "tailleEnCm",  "", $joueur['tailleEnCm']);
    $formulaire->setText("Poids (en Kg)", "poidsEnKg", "",  $joueur['poidsEnKg']);
    $formulaire->setSelect("Statut", ['ACTIF', 'BLESSE', 'ABSENT', 'SUSPENDU'], "statut", $joueur['statut']);
    $formulaire->addButton("Submit", "update", "modifier", "Modifier");
    echo $formulaire;
}