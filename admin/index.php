<?php
session_start();

include_once('../php/ferme.class.php');
include_once('php/view.class.php');

$ferme = new Ferme("../ferme.config.php");
$view  = new View($ferme);

//Pour éviter les problèmes de chemin : 
$ferme->config['ferme_path'] = "../".$ferme->config['ferme_path'];

$ferme->refresh();

if(isset($_GET['action'])){
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
				try {
					$ferme->save($_GET['name']);
				} catch (Exception $e) {
					$view->addAlert($e->getMessage(),"error");
					header("Location: ".$ferme->getURL());
					exit;
				}
			
				$view->addAlert("Wiki ".$_GET['name']." : Sauvegardé avec succès");
				header("Location: ".$ferme->getURL());
				exit;
			break;

		default:
			# rien, vraiment...
			break;
	}
}

$view->show();

?>