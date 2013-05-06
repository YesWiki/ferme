<?php
class View{
	
	protected $ferme;
	protected $alerts;
	protected $theme;

	/************************************************************************
	 * Constructeur
	 ***********************************************************************/
	function __construct($ferme){
		$this->ferme = $ferme;
		$this->alerts = array();

		$this->theme = $this->ferme->config['template'];
	}
	
	/************************************************************************
	 * Affiche le wiki. ($template permet de forcer le theme)
	 ***********************************************************************/
	//TODO : Ajouter choix du template
	function show($template = ""){

		if($template == "")
			$template = $this->theme;
		else 
			$this->theme = $template;

		$squelette_path = "themes/".$template."/squelette/".$template.".phtml";

		if(!is_file($squelette_path)) {
			die("Template introuvable. (".$squelette_path.").");
		}
		include($squelette_path);	
	}
	
	/************************************************************************
	 * Affiche la liste des Themes selon le template fournis
	 ***********************************************************************/
	private function printThemes($template = "theme.phtml"){
		$themesList = $this->ferme->getThemesList();
		$i = 0;
		foreach ($themesList as $theme) {
			include("themes/".$this->theme."/squelette/".$template);
		}
		unset($themesList);
	}

	/************************************************************************
	 * Affiche la liste des Wikis selon le template fournis
	 ***********************************************************************/
	private function printWikis($template = "wiki.phtml"){

		if($this->ferme->nbWikis() <= 0) return;

		$this->ferme->resetIndex();
		do {
			$wiki = $this->ferme->getCur();
			$infos = $wiki->getInfos(); 
			include("themes/".$this->theme."/squelette/".$template);

		} while ($this->ferme->getNext());		
	}

	/************************************************************************
	 * Affiche la liste des Archives selon le template fournis
	 ***********************************************************************/
	private function printArchives($template = "archive.phtml"){

		if($this->ferme->nbArchives() <= 0) return;

		$this->ferme->resetIndexArchives();
		do {
			$archive = $this->ferme->getCurArchive(); 
			$infos = $archive->getInfos();
			include("themes/".$this->theme."/squelette/".$template);

		} while ($this->ferme->getNextArchive());		
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
	 * Affiche la liste des alertes selon le template fournis.
	 ***********************************************************************/
	function printAlerts($template = "alert.phtml"){
		//Affichage des alertes
		if (isset($_SESSION['alerts'])){
			$i = 0;
			foreach ($_SESSION['alerts'] as $key => $alert){
				$id = "alert".$key; 
				include("themes/".$this->theme."/squelette/".$template);
			}
		}
		unset($_SESSION['alerts']); //pour éviter qu'elle ne s'accumulent.
	}

	/************************************************************************
	 * Ajoute une alerte a afficher.
	 ***********************************************************************/
	function addAlert($text, $type="default"){
		if (!isset($_SESSION['alerts'])) {
			$_SESSION['alerts'] = array();
		}

		$_SESSION['alerts'][] = array(
				'text' => $text,
				'type' => $type,
			);

	}

	/***********************************************************************
	 * Envois un email de confirmation
	 **********************************************************************/
	//TODO : ne valider l'envois que si le paramêtre mail est a 1 dans la 
	// configuration.
	function sendConfirmationMail($mail, $wikiName){

	}

	/***********************************************************************
	 * Ajoute les CSS du themes
	 **********************************************************************/
	function printCSS(){
		$css_path = "themes/".$this->theme."/css/";
		foreach ($this->getFiles($css_path) as $file) {
			print("<link href=\"".$file."\" rel=\"stylesheet\">\n");
		}
	}

	/***********************************************************************
	 * Ajoute les JavaScript du themes
	 **********************************************************************/
	function printJS(){
		$js_path = "themes/".$this->theme."/js/";
		foreach ($this->getFiles($js_path) as $file) {
			print("<script src=\"".$file."\"></script>\n");
		}
	}

	/***********************************************************************
	 * Liste des fichiers dans un repertoire
	 **********************************************************************/
	//TODO : Filtrer les résultat par extension
	private function getFiles($path){
		$file_array = array();
		if ($handle = opendir($path)) {
			while (false !== ($entry = readdir($handle))) {
				$entry_path = $path.$entry;
				if ($entry != "." && $entry != ".." 
								  && is_file($entry_path)){
					
					$file_array[] = $entry_path;
				}
			}
			closedir($handle);
		}
		return $file_array;
	}
	
}


?>
