<?php
namespace Ferme;

$loader = require __DIR__ . '/vendor/autoload.php';

session_start();

if (!file_exists('vendor')) {
    throw new \Exception(
        'Vous devez executer "composer install" dans le dossier de la Ferme.',
        1
    );
}

if (!file_exists('wikis')) {
    mkdir('wikis', 0777, true);
}

if (!file_exists('archives')) {
    mkdir('archives', 0777, true);
}

if (!file_exists('ferme.config.php')) {
    throw new \Exception(
        'Le fichier de configuration est absent.',
        1
    );
}

try {
    $config = new Configuration('ferme.config.php');
    $ferme = new Ferme($config);
    $controller = new Controller($ferme);
} catch (\Exception $e) {
    print('Erreur fatale (problème de configuration ?)');
    exit;
}

$controller->run($_GET, $_POST);
