<h1>Modifier une rencontre</h1>

<?php

use R301\Modele\Rencontre\RencontreLieu;
use R301\Vue\Component\Formulaire;
use R301\Controleur\RencontreControleur;

if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_GET['id'])
        && isset($_POST['dateHeure'])
        && isset($_POST['equipeAdverse'])
        && isset($_POST['adresse'])
        && isset($_POST['lieu'])
) {
    $id = (int)$_GET['id'];

    $context = stream_context_create([
        'http' => [
            'method'  => 'PUT',
            'header'  => "Content-Type: application/json\r\nAuthorization: Bearer " . $_SESSION['jwt'],
            'content' => json_encode([
                'dateHeure'     => $_POST['dateHeure'],
                'equipeAdverse' => $_POST['equipeAdverse'],
                'adresse'       => $_POST['adresse'],
                'lieu'          => $_POST['lieu'],
            ]),
            'ignore_errors'   => true,
            'follow_location' => 0,
        ]
    ]);

    $response = file_get_contents('http://localhost/Projet_R4.01/backend/EndpointRencontre.php?id=' . $id, false, $context);
    $result = json_decode($response, true);

    if (isset($result['status_code']) && $result['status_code'] === 200) {
        header('Location: /Projet_R4.01/rencontre');
        exit();
    } else {
        $erreur = $result['status_message'] ?? "Erreur lors de la modification de la rencontre";
    }

} else {
    if (!isset($_GET['id'])) {
        header("Location: /Projet_R4.01/rencontre");
        exit();
    } else {
        $id = (int)$_GET['id'];

        // Use controller directly to prefill the form
        $controleur = RencontreControleur::getInstance();
        $rencontre = $controleur->getRenconterById($id);

        if ($rencontre === null) {
            header("Location: /Projet_R4.01/rencontre");
            exit();
        }

        $formulaire = new Formulaire("/Projet_R4.01/rencontre/modifier?id=" . $rencontre->getRencontreId());
        $formulaire->setDateTime("Date", "dateHeure", date("Y-m-d H:i"), $rencontre->getDateEtHeure()->format("Y-m-d H:i"));
        $formulaire->setText("Equipe adverse", "equipeAdverse", "", $rencontre->getEquipeAdverse());
        $formulaire->setText("Adresse", "adresse", "", $rencontre->getAdresse());
        $formulaire->setSelect("Lieu", array_map(function (RencontreLieu $lieu) {
            return $lieu->name;
        }, RencontreLieu::cases()), "lieu", $rencontre->getLieu()->name);
        $formulaire->addButton("Submit", "update", "Valider", "Modifier");
        echo $formulaire;
    }
}

if (isset($erreur)) {
    echo "<p style='color:red;'>" . htmlspecialchars($erreur) . "</p>";
}
?>