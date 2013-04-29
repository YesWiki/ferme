<?php
class View{
	
	protected $ferme;
	protected $alertes;
	protected $theme;

	/************************************************************************
	 * Constructeur
	 ***********************************************************************/
	function __construct($ferme){
		$this->ferme = $ferme;
		$this->alertes = array();
	}
	
	/************************************************************************
	 * Affiche le wiki.
	 ***********************************************************************/
	//TODO : Ajouter choix du template
	function showNewWiki($wikiName = "", $mail = "", $description = ""){
		$template = "php/views/".$this->ferme->config['template'];

		if(!is_file($template)) {
			die("Template introuvable. (php/views/".$this->ferme->config['template'].").");
		}
		include("php/views/".$this->ferme->config['template']);	
	}

	
	/************************************************************************
	 * Affiche la liste des Themes selon le template fournis
	 ***********************************************************************/
	private function printThemesList($template = "theme.phtml"){
		$themesList = $this->ferme->getThemesList();
		$i = 0;
		foreach ($themesList as $theme) {
			include("php/views/".$template);
		}
		unset($themesList);
	}

	/************************************************************************
	 * Affiche la liste des wikis selon le template fournis 
	 * et l'ordre demandé
	 ***********************************************************************/
	private function printWikisList($order = 'none', $template = "wiki.phtml"){
		$listWikis = $this->ferme->getWikisList('name');

		foreach($listWikis as $wiki) {
			include("php/views/".$template);
		}
		unset($wiki);
	}


	/************************************************************************
	 * Affiche la liste des alertes selon le template fournis.
	 ***********************************************************************/
	private function printAlertesList($template = "alerte.phtml"){
		//Affichage des alertes
		if(!empty($this->alertes)){
			$i = 0;
			foreach ($this->alertes as $alerte)
				$id = "alerte".$i; 
				include("php/views/".$template);
				$i++;
			unset($alerte);
		}
	}

	/************************************************************************
	 * HASH-CASH : Charge le JavaScript qui génére la clé.
	 ***********************************************************************/
	private function HashCash(){	
		
		//TODO : Rendre ce code "portable"
		echo '<!--Protection HashCash -->
		<script type="text/javascript" 
				src="'.$this->ferme->config['base_url'].'php/wp-hashcash-js.php?siteurl='.$this->ferme->config['base_url'].'">
		</script>';
	}

	/************************************************************************
	 * Ajoute une alerte a afficher.
	 ***********************************************************************/
	//TODO : Gérer les alertes dans le model.
	function addAlerte($text){
		$this->alertes[] = $text;
	}

	/***********************************************************************
	 * Envois un email de confirmation
	 **********************************************************************/
	//TODO : ne valider l'envois que si le paramêtre mail est a 1 dans la 
	// configuration.
	function sendConfirmationMail($mail, $wikiName){

	}
	
}


?>
