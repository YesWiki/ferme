<?php
namespace Ferme;

$loader = require __DIR__ . '/vendor/autoload.php';

session_start();
try {
    $controller = new Controller();
} catch (\Exception $e) {
    print('Erreur fatale (problème de configuration ?)');
    exit;
}

$controller->run($_GET, $_POST);
