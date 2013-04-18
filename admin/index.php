<?php
session_start();

include_once('php/ferme.class.php');
include_once('php/view.class.php');

$ferme = new Ferme("../ferme.config.php");
$view = new View($ferme);

switch ($_GET['action']) {
	case 'delete':
		if(isset($_GET['name'])){
			$ferme->delete($_GET['name']);
			$view->addAlert("Wiki ".$_GET['name']." : Supprimé avec succès");
			header("Location: ".$ferme->getURL());
			exit;
		}
		break;

	case 'save':
		if(isset($_GET['name']))
			$ferme->save($_GET['name']);
			$view->addAlert("Wiki ".$_GET['name']." : Sauvegardé avec succès");
			header("Location: ".$ferme->getURL());
		break;

	default:
		# rien, vraiment...
		break;
}

$view->show();

?>