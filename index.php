<?php
namespace Ferme;

if (!is_dir('vendor')) {
    print('Vous devez executer "composer install" dans le dossier de la Ferme.');
    exit;
}
$loader = require __DIR__ . '/vendor/autoload.php';

session_start();

if (!is_file('ferme.config.php')) {
    throw new \Exception(
        'Le fichier de configuration est absent.',
        1
    );
}
$config = new Configuration('ferme.config.php');

try {
    $ferme = new Ferme($config);
} catch (\Exception $e) {
    print('Erreur fatale (problème de configuration ?)<br />');
    print($e->getMessage());
    exit;
}

try {
    $ferme->checkInstallation();
} catch (\Exception $e) {
    print($e->getMessage());
    exit;
}

$controller = new Controller($ferme);
$controller->run($_GET, $_POST);
