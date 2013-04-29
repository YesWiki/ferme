<?php
	session_start();

	include("php/ferme.class.php");
	include("php/view.class.php");

	$ferme = new Ferme("ferme.config.php");
	$view = new View($ferme);

	$ferme->refresh();

	//test des alertes

	if (isset($_POST['action']) && isset($_POST['wikiName'])) {

		try {
			$wikiPath = $ferme->add($_POST['wikiName'], $_POST['mail'], $_POST['description']);
		} catch(Exception $e){
			$view->addAlert($e->getMessage());
			$view->show();
			die();
		}

		/*********************************************************************
		 * Envois email.
		 ********************************************************************/
		mail($_POST["mail"], 
		"Création du wiki ".$_POST["wikiName"], 
		"Bonjour, 

Votre wiki : ".$_POST["wikiName"]." a été planté avec succès. 
Vous le trouverez a l'adresse : ".$ferme->config["base_url"].$wikiPath."

Pour toute information complémentaire n'hésitez pas à contacter :
 - christian.resche@supagro.inra.fr
 - florestan.bredow@supagro.inra.fr

 Cordialement.",
 		'From: no-reply@cdrflorac.fr' . "\r\n" );
 		/********************************************************************/


		$view->addAlert('<a href="'.$ferme->config["base_url"].$wikiPath.'">Visiter le nouveau wiki</a>');
		//Recharge la page pour ne pas conserver les données du formulaire
		header("Location: ".$ferme->getURL());
		exit;
	}

	$view->show();


?>
