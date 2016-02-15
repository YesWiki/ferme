<?php
namespace Ferme;

$loader = require __DIR__ . '/vendor/autoload.php';

session_start();

$controller = new Controller();
$controller->run($_GET, $_POST);
