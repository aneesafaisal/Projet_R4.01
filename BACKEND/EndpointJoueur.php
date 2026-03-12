<?php
    require_once 'Psr4AutoloaderClass.php';

    use R301\Psr4AutoloaderClass;

    $loader = new Psr4AutoloaderClass();
    $loader->register();
    $loader->addNamespace('R301', __DIR__);

    use R301\Controleur\JoueurControleur;
    use R301\Modele\Joueur\Joueur;
    use R301\Modele\Joueur\JoueurDAO;
    use R301\Modele\Joueur\JoueurStatut;

    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    
    function deliver_response($status_code, $status_message, $data=null){
        /// Paramétrage de l'entête HTTP
        http_response_code($status_code); //Utilise un message standardisé en fonction du code HTTP
        //header("HTTP/1.1 $status_code $status_message"); //Permet de personnaliser le message associé au code HTTP
        header("Content-Type:application/json; charset=utf-8");//Indique au client le format de la réponse
        header("Access-Control-Allow-Origin: *");
        $response['status_code'] = $status_code;
        $response['status_message'] = $status_message;
        $response['data'] = $data;
        /// Mapping de la réponse au format JSON
        $json_response = json_encode($response);
        if($json_response===false)
            die('json encode ERROR : '.json_last_error_msg());
        /// Affichage de la réponse (Retourné au client)
        echo $json_response;
    }

    $controleur = JoueurControleur::getInstance();

    /// Identification du type de méthode HTTP envoyée par le client
    $http_method = $_SERVER['REQUEST_METHOD'];
    try {
        switch ($http_method){
            case "OPTIONS" :
                deliver_response("204", "Méthode OPTIONS autorisée - Requête CORS acceptée");
                break;

            case "GET" :
                //Récupération des données dans l’URL si nécessaire
                if(isset($_GET['id']))
                {
                    $id=htmlspecialchars($_GET['id']);
                    
                    $joueur = $controleur->getJoueurById($id);
                    if ($joueur === null) {
                        deliver_response(404, "Joueur non trouvé");
                    } else {
                        deliver_response(200, "La requête a réussi", $joueur);
                    }
                    break;
                }
                $joueurs = $controleur->listerTousLesJoueurs();
                deliver_response("200", "La requête a réussi.", $joueurs);
            break;

            case "POST":
                $postedData = file_get_contents('php://input');
                $data = json_decode($postedData, true);

                if (!$data || !is_array($data)) {
                    deliver_response(400, "JSON invalide");
                    break;
                }

                if (!isset($data['nom'], $data['prenom'], $data['numeroDeLicence'], $data['dateDeNaissance'], $data['tailleEnCm'], $data['poidsEnKg'], $data['statut'])) {
                    deliver_response(400, "Champs manquants");
                    break;
                }

                try {
                    $date = new DateTime($data['dateDeNaissance']);
                } catch (Exception $e) {
                    deliver_response(400, "Format de date invalide (YYYY-MM-DD attendu)");
                    break;
                }

                $success = $controleur->ajouterJoueur(
                    $data['nom'], $data['prenom'], $data['numeroDeLicence'], $date,
                    (int)$data['tailleEnCm'], (int)$data['poidsEnKg'], $data['statut']
                );

                deliver_response($success ? 201 : 400, $success ? "Joueur créé" : "Erreur lors de la création");
                break;

            case "PUT":
                if (!isset($_GET['id'])) {
                    deliver_response(400, "ID manquante");
                    break;
                }

                $id = (int) $_GET['id'];

                $postedData = file_get_contents('php://input');
                $data = json_decode($postedData, true);

                if (!$data || !is_array($data)) {
                    deliver_response(400, "JSON invalide");
                    break;
                }

                if (!isset($data['nom'], $data['prenom'], $data['numeroDeLicence'], $data['dateDeNaissance'], $data['tailleEnCm'], $data['poidsEnKg'], $data['statut'])) {
                    deliver_response(400, "Champs manquants");
                    break;
                }

                $joueurExistant = $controleur->getJoueurById($id);
                if ($joueurExistant === null) {
                    deliver_response(404, "Joueur non trouvé");
                    break;
                }

                try {
                    $date = new DateTime($data['dateDeNaissance']);
                } catch (Exception $e) {
                    deliver_response(400, "Format de date invalide");
                    break;
                }

                $success = $controleur->modifierJoueur(
                    $id, $data['nom'], $data['prenom'], $data['numeroDeLicence'],
                    $date, (int)$data['tailleEnCm'], (int)$data['poidsEnKg'], $data['statut']
                );

                deliver_response($success ? 200 : 400, $success ? "Joueur mis à jour" : "Erreur lors de la mise à jour");
                break;

            case "DELETE":
                if (!isset($_GET['id'])) {
                    deliver_response(400, "ID manquante");
                    break;
                }

                $id = (int)$_GET['id'];   // cast direct + suppression de htmlspecialchars (inutile pour un ID)

                $delete = $controleur->supprimerJoueur($id);

                if ($delete === true) {
                    deliver_response(204, "La requête a réussi.");
                } else {
                    deliver_response(404, "Joueur non trouvé");
                }
            break;
        }
    } catch (Throwable $e) {   // ← Exception → Throwable
        deliver_response(500, "Erreur serveur : " . $e->getMessage());
    }

?>