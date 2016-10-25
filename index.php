<?php
namespace Ferme;

$loader = require __DIR__ . '/vendor/autoload.php';

session_start();

try {
    $config = new Configuration('ferme.config.php');
    $ferme = new Ferme($config);
    $controller = new Controller($ferme);
} catch (\Exception $e) {
    print('Erreur fatale (problème de configuration ?)');
    exit;
}

$controller->run($_GET, $_POST);
