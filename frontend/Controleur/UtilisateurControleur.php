<?php

namespace R301\Controleur;

class UtilisateurControleur {
    private static ?UtilisateurControleur $instance = null;
    private string $authApiUrl = "https://auth.alwaysdata.net/EndpointAuth.php";

    // Token vide ici (c’est ce contrôleur qui va le récupérer)
    private string $token = "";

    private function __construct() {}

    public static function getInstance(): UtilisateurControleur {
        if (self::$instance === null) {
            self::$instance = new UtilisateurControleur();
        }
        return self::$instance;
    }

    private function callAPI(string $method, string $url, array $data = null, bool $withToken = false): ?array {
        $curl = curl_init();

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        // On n'envoie PAS le token pour la connexion
        if ($withToken && $this->token !== "") {
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token
            ]);
        }

        $result = curl_exec($curl);
        curl_close($curl);

        if (!$result) return null;

        return json_decode($result, true);
    }

    public function seConnecter(string $username, string $password): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $data = [
            "login"    => $username,
            "password" => $password
        ];

        $response = $this->callAPI("POST", $this->authApiUrl, $data);

        if (!$response || !isset($response["token"])) {
            return false;
        }

        $jwt = $response["token"];

        // Stockage en session + cookie
        $_SESSION['token']    = $jwt;
        $_SESSION['username'] = $username;

        setcookie(
            "token",
            $jwt,
            [
                "expires"  => time() + 3600,
                "path"     => "/",
                "httponly" => true,
                "secure"   => false,
                "samesite" => "Strict"
            ]
        );

        // On met à jour le token dans le contrôleur pour les futurs appels
        $this->token = $jwt;

        return true;
    }

    public function seDeconnecter(): void {
        setcookie("token", "", time() - 3600, "/");

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();

        $this->token = "";
    }
}