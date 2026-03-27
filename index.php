<?php
require_once __DIR__ . '/Psr4AutoloaderClass.php';
use R301\Psr4AutoloaderClass;

define('BASE_URL', '/Projet_R4.01');

$loader = new Psr4AutoloaderClass;
$loader->register();
$loader->addNamespace('R301', __DIR__ . '/FRONTEND');
$loader->addNamespace('R301', __DIR__ . '/BACKEND');


if (preg_match('/\.(?:png|jpg|jpeg|gif|ico|css|js)\??.*$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

session_start(); 

$uri = strtok($_SERVER["REQUEST_URI"], '?');
$uri = str_replace(BASE_URL, '', $uri);

if ($uri === '/' || $uri === '/index.php' || $uri === '') {
    $uri = '/tableauDeBord';
}

if ($uri !== "/login" && !isset($_SESSION['username'])) {
    header('Location: ' . BASE_URL . '/login');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>R3.01</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" charset="UTF-8"/>
        <link rel="stylesheet" href="<?= BASE_URL ?>/stylesheet.css"/>
        <link rel="icon" type="image/jpg" href="<?= BASE_URL ?>/favicon.jpg">
    </head>
    <body>
    <?php if ($uri !== '/login') : ?>
        <nav class="navbar">
            <a href="<?= BASE_URL ?>/tableauDeBord" class="dropbtn">Tableau de bord</a>
            <div class="dropdown">
                <button class="dropbtn">Joueurs</button>
                <div class="dropdown-content">
                    <a href="<?= BASE_URL ?>/joueur/ajouter">Ajouter un joueur</a>
                    <a href="<?= BASE_URL ?>/joueur">Liste de joueurs</a>
                </div>
            </div>
            <div class="dropdown">
                <button class="dropbtn">Rencontres</button>
                <div class="dropdown-content">
                    <a href="<?= BASE_URL ?>/rencontre/ajouter">Ajouter une rencontre</a>
                    <a href="<?= BASE_URL ?>/rencontre">Liste des rencontres</a>
                </div>
            </div>
        </nav>
    <?php endif; ?>
    <?php
        require_once __DIR__ . '/frontend' . $uri . '.php';
    ?>
    </body>
</html>