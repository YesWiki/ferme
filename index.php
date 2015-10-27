<?php
namespace Ferme;

$loader = require __DIR__ . '/vendor/autoload.php';

session_start();

$config = new Configuration('ferme.config.php');
$ferme = new Ferme($config);
$view = new View($ferme);

$ferme->refresh(false); //refesh without calculating size (db & files)

if (isset($_POST['action'])
    && isset($_POST['wikiName'])
    && isset($_POST['mail'])
) {
    //HashCash protection
    require_once 'app/secret/wp-hashcash.php';
    if (!isset($_POST["hashcash_value"])
        || hashcash_field_value() != $_POST["hashcash_value"]) {
        $view->addAlert(
            "La plantation de wiki est une activité délicate qui ne doit pas"
            . " être effectuée par un robot. (Pensez à activer JavaScript)"
        );
        header("Location: " . $ferme->getURL());
        exit();
    }

    //Une série de tests sur les données.
    if ($ferme->isValidWikiName($_POST['wikiName'])) {
        $view->addAlert("Ce nom wiki n'est pas valide.");
        header("Location: " . $ferme->getURL());
        exit();
    }

    if (!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)) {
        $view->addAlert("Cet email n'est pas valide.");
        header("Location: " . $ferme->getURL());
        exit();
    }

    try {
        $wiki_path = $ferme->add(
            $_POST['wikiName'],
            $_POST['mail'],
            $_POST['description']
        );
    } catch (\Exception $e) {
        $view->addAlert($e->getMessage());
        header("Location: " . $ferme->getURL());
        exit;
    }

    $view->addAlert(
        '<a href="' . $config->getParameter('base_url')
        . $wiki_path . '">Visiter le nouveau wiki</a>'
    );

    // Reload page to clean form.
    header("Location: " . $ferme->getURL());
    exit;
}

$view->show();
