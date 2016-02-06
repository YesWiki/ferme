<?php
namespace Ferme;

$loader = require __DIR__ . '/vendor/autoload.php';

session_start();

$controller = new Controller($_GET, $_POST);
$controller->run();
