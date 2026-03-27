<?php

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"]) && isset($_POST["password"])) {
    
    $login = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/json',
            'content' => json_encode(['login' => $login, 'password' => $password]),
            'ignore_errors' => true,
            'follow_location' => 0,
        ]
    ]);

    $response = file_get_contents('http://localhost/Projet_R4.01/authentification/EndpointAuth.php', false, $context);

    $result = json_decode($response, true);

    if (isset($result['token'])) {
        $_SESSION['jwt'] = $result['token'];
        $_SESSION['username'] = $login;
        header("Location: /Projet_R4.01/joueur");
        exit();
    } else {
        $erreur = isset($result['message']) ? $result['message'] : "Le nom d'Utilisateur ou le mot de passe est incorrect";
    }
}
?>

<body>
    <div class="CentredContainer">
        <h1>Login</h1>
        <div class="container">
            <form action="/Projet_R4.01/login" method="post">
                <div class="row">
                    <div class="col-20">
                        <label for="username">Username : </label>
                    </div>
                    <div class="col-80">
                        <input type="text" id="username" name="username"/><br> 
                    </div>
                </div> 
                <div class="row">
                    <div class="col-20">
                        <label for="password">Password : </label>
                    </div>
                    <div class="col-80">
                        <input type="password" id="pass" name="password"/><br>
                    </div>
                </div>
                <div class="row">
                    <input type="submit" value="Login"/>
                </div>
            </form>
        </div>
        <p><?php if (isset($erreur)) { echo $erreur; } ?></p>
    </div>
</body>
</html>
