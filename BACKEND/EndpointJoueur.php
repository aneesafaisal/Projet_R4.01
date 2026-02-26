<?php
    use R301\Controleur\JoueurControleur;
    use R301\Modele\Joueur\Joueur;
    use R301\Modele\Joueur\JoueurDAO;
    use R301\Modele\Joueur\JoueurStatut;

    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    
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

            case "POST" :
                //Récupération des données dans le corps
                // Récupération des données dans le corps
                $postedData = file_get_contents('php://input');
                $data = json_decode($postedData,true);
                if (!$data) {
                    deliver_response(400, "JSON invalide");
                    break;
                } 
                $date = new DateTime($data['dateDeNaissance']);

                $insert = $controleur->ajouterJoueur(
                    $data['nom'],
                    $data['prenom'],
                    $data['numeroDeLicence'],
                    $date,
                    (int)$data['tailleEnCm'],
                    (int)$data['poidsEnKg'],
                    $data['statut']
                );

                if ($insert) {
                    deliver_response(201, "Joueur créer");
                } else {
                    deliver_response(400, "Erreur lors du traitement.");
                }
            break;

            case "PUT" :
                //Récupération des données dans le corps
                if(isset($_GET['id']))
                {
                    $id=htmlspecialchars($_GET['id']);

                    //Traitement des données
                    // Récupération des données dans le corps
                    $postedData = file_get_contents('php://input');
                    $data = json_decode($postedData,true);
                    if (!$data) {
                        deliver_response(400, "JSON invalide");
                        break;
                    } 
                    if (empty($controleur->getJoueurById($id))){
                        $date = new DateTime($data['dateDeNaissance']);
                        $insert = $controleur->ajouterJoueur(
                            $data['nom'],
                            $data['prenom'],
                            $data['numeroDeLicence'],
                            $date,
                            (int)$data['tailleEnCm'],
                            (int)$data['poidsEnKg'],
                            $data['statut']
                        );
                    } else {
                        $insert = $controleur->modifierJoueur($id, $data['nom'], $data['prenom'] , $data['numeroDeLicence'], 
                                                    $data['dateDeNaissance'], $data['tailleEnCm'], $data['poidsEnKg'], $data['statut']);
                    }
                    if ($insert) {
                        deliver_response(200, "La requête a réussi.");
                    } else {
                        deliver_response(400, "Erreur lors du traitement.");
                    }
                    break;
                }
                deliver_response("400", "ID manquante");
            break;

            case "DELETE" :

                if(isset($_GET['id']))
                {
                    $id=htmlspecialchars($_GET['id']);

                    $delete=$controleur->supprimerJoueur($id);
                    if ($delete == "Succes"){
                        deliver_response(204, "La requête a réussi.");
                    } else {
                        deliver_response(400, "Syntaxe de la requête non conforme");
                    }
                    break;
                }
                deliver_response(400, "ID manquante");
            break;
        }
    } catch (Exception $e) {
        deliver_response(500, "Erreur serveur : " . $e->getMessage());
    }

    /// Envoi de la réponse au Client
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

?>