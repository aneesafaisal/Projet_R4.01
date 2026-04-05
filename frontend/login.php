<?php
// ====================== GESTION DU LOGIN ======================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["username"]) && isset($_POST["password"])) {

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $controleur = R301\Controleur\UtilisateurControleur::getInstance();

    if ($controleur->seConnecter($username, $password)) {
        // Redirection vers le tableau de bord (comme dans ton index.php)
        header("Location: " . BASE_URL . "/tableauDeBord");
        exit();
    } else {
        $erreur = "Nom d'utilisateur ou mot de passe incorrect";
    }
}
?>

<div class="CentredContainer">
    <h1>Login</h1>
    <div class="container">
        <form action="<?= BASE_URL ?>/login" method="post">
            <div class="row">
                <div class="col-20">
                    <label for="username">Username :</label>
                </div>
                <div class="col-80">
                    <input type="text" id="username" name="username" required><br>
                </div>
            </div>
            <div class="row">
                <div class="col-20">
                    <label for="password">Password :</label>
                </div>
                <div class="col-80">
                    <input type="password" id="password" name="password" required><br>
                </div>
            </div>
            <div class="row">
                <input type="submit" value="Se connecter" />
            </div>
        </form>
    </div>
    <?php if (isset($erreur)): ?>
        <p style="color:red; font-weight:bold;"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>
</div>