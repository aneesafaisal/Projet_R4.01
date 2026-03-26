<h1>Ajouter une rencontre</h1>

<?php

use R301\Modele\Rencontre\RencontreLieu;
use R301\Vue\Component\Formulaire;

if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST['dateHeure'])
        && isset($_POST['equipeAdverse'])
        && isset($_POST['adresse'])
        && isset($_POST['lieu'])
) {
    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
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

    $response = file_get_contents('http://localhost/Projet_R4.01/backend/EndpointRencontre.php', false, $context);

    $result = json_decode($response, true);

    if (isset($result['status_code']) && $result['status_code'] === 201) {
        header('Location: /Projet_R4.01/rencontre');
        exit();
    } else {
        $erreur = $result['status_message'] ?? "Erreur lors de la création de la rencontre";
        error_log("Erreur API: " . $erreur);
    }

} else {
    $formulaire = new Formulaire("/Projet_R4.01/rencontre/ajouter");
    $formulaire->setDateTime("Date", "dateHeure", date("Y-m-d H:i"));
    $formulaire->setText("Equipe adverse", "equipeAdverse");
    $formulaire->setText("Adresse", "adresse");
    $formulaire->setSelect("Lieu", array_map(function(RencontreLieu $lieu) { return $lieu->name; }, RencontreLieu::cases()), "lieu");
    $formulaire->addButton("Submit", "create", "Valider", "Modifier");
    echo $formulaire;
}

if (isset($erreur)) {
    echo "<p style='color:red;'>" . htmlspecialchars($erreur) . "</p>";
}
?>