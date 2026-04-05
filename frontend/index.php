<?php
require_once __DIR__ . '/Psr4AutoloaderClass.php';
use R301\Psr4AutoloaderClass;

define('BASE_URL', '');

$loader = new Psr4AutoloaderClass;
$loader->register();
$loader->addNamespace('R301', __DIR__ . '/FRONTEND');

if (preg_match('/\.(?:png|jpg|jpeg|gif|ico|css|js)\??.*$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

session_start();

$uri = strtok($_SERVER["REQUEST_URI"], '?');
// No need to strip BASE_URL since it's empty now

if ($uri === '/' || $uri === '/index.php' || $uri === '') {
    $uri = '/tableauDeBord';
}

if ($uri !== "/login" && !isset($_SESSION['username'])) {
    header('Location: /login');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <title>R3.01</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" charset="UTF-8" />
    <link rel="stylesheet" href="/stylesheet.css" />
    <link rel="icon" type="image/jpg" href="/favicon.jpg">
</head>

<body>
    <?php if ($uri !== '/login'): ?>
        <nav class="navbar">
            <a href="/tableauDeBord" class="dropbtn">Tableau de bord</a>
            <div class="dropdown">
                <button class="dropbtn">Joueurs</button>
                <div class="dropdown-content">
                    <a href="/joueur/ajouter">Ajouter un joueur</a>
                    <a href="/joueur">Liste de joueurs</a>
                </div>
            </div>
            <div class="dropdown">
                <button class="dropbtn">Rencontres</button>
                <div class="dropdown-content">
                    <a href="/rencontre/ajouter">Ajouter une rencontre</a>
                    <a href="/rencontre">Liste des rencontres</a>
                </div>
            </div>
        </nav>
    <?php endif; ?>
    <?php
    require_once __DIR__ . '/FRONTEND' . $uri . '.php';
    ?>
</body>

</html>