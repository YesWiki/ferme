<?php

class View{
	
	private $ferme;
	protected $alerts;
	
	function __construct($ferme) {
		$this->ferme = $ferme;
		$this->alertes = array();
	}

	function show(){
		include("php/views/default.phtml");
	}

	/************************************************************************
	 * Affiche la liste des alertes selon le template fournis.
	 ***********************************************************************/
	function printAlerts($template = "alert.phtml"){
		//Affichage des alertes
		if (isset($_SESSION['alerts'])){
			$i = 0;
			foreach ($_SESSION['alerts'] as $key => $alert){
				$id = "alert".$key; 
				include("php/views/".$template);
			}
			unset($_SESSION['alerts'][$key]);
		}
	}

	/************************************************************************
	 * Ajoute une alerte a afficher.
	 ***********************************************************************/
	function addAlert($text, $type="default"){
		/*$this->alerts[] = array(
			'text' => $text,
			'type' => $type,
			);*/
		if (!isset($_SESSION['alerts'])) {
			$_SESSION['alerts'] = array();
		}

		$_SESSION['alerts'][] = array(
				'text' => $text,
				'type' => $type,
			);

	}
}

?>