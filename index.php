<?php

	include("php/ferme.class.php");
	include("php/view.class.php");

	$farm = new Ferme("ferme.config.php");
	$view = new View($farm);

	$farm->refresh();

	//test des alertes

	if (isset($_POST['action']) && isset($_POST['wikiName'])) {

		try {
			$wikiPath = $farm->installWiki($_POST['wikiName'], $_POST['mail'], $_POST['description']);
		} catch(Exception $e){
			$view->addAlerte($e->getMessage());
			$view->showNewWiki();
			die();
		}

		/*********************************************************************
		 * Envois email.
		 ********************************************************************/
		mail($_POST["mail"], 
		"Création du wiki ".$_POST["wikiName"], 
		"Bonjour, 

Votre wiki : ".$_POST["wikiName"]." a été planté avec succès. 
Vous le trouverez a l'adresse : ".$farm->config["base_url"].$wikiPath."

Pour toute information complémentaire n'hésitez pas à contacter :
 - christian.resche@supagro.inra.fr
 - florestan.bredow@supagro.inra.fr

 Cordialement.",
 		'From: no-reply@cdrflorac.fr' . "\r\n" );
 		/********************************************************************/


		$view->addAlerte('<a href="'.$farm->config["base_url"].$wikiPath.'">Visiter le nouveau wiki</a>');
	}

	$view->showNewWiki();


?>
