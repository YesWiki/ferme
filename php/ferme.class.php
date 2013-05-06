<?php

include_once('wiki.class.php');
include_once('archive.class.php');

class Ferme {
	
	public $config; //TODO : devrait devenir privé
	private $wikis;
	private $archives;

	/*******************************************************************
	 * constructeur
	 * ****************************************************************/	
	function __construct($configPath) {
		include($configPath);
		$this->wikis = array();
		$this->archives = array();

		// TODO : Ce qui suit est un bricolage infame qui permet de travailler 
		// avec lampp, dont les executables ne sont pas dans /usr/bin
		// Il faut absolument trouver un autre moyen de passer cette valeur 
		// aux classes Wiki et Archive
		$GLOBALS['exec_path'] = $this->config['exec_path'];
	}

	/*************************************************************************
	 * Charge la liste des wikis et leur config
	 ************************************************************************/
	function refresh(){
		$this->wikis = array();
		$ferme_path = $this->config['ferme_path'];

		if ($handle = opendir($ferme_path)) {
			while (false !== ($entry = readdir($handle))) {
				$entry_path = $ferme_path.$entry;
				if ($entry != "." && $entry != ".." 
								  && is_dir($entry_path)){
					
					$this->wikis[$entry] = new Wiki($entry_path);
				}
			}
			closedir($handle);
		} else
			throw new Exception("Impossible d'accéder à "
								.$ferme_path, 1);
			
	}

	/*************************************************************************
	 * Charge la liste des archives
	 ************************************************************************/
	function refreshArchives(){
		$this->archives = array();
		$archives_path = $this->config['admin_path'];

		if ($handle = opendir($archives_path)) {
			while (false !== ($entry = readdir($handle))) {
				$entry_path = $archives_path.$entry;
				if ($entry != "." && $entry != ".." 
								  && is_file($entry_path)){

					$this->archives[$entry] = new Archive($entry);
				}
			}
			closedir($handle);
		} else
			throw new Exception("Impossible d'accéder à "
								.$archives_path, 1);
	}

	function nbWikis(){return count($this->wikis);}
	function nbArchives(){return count($this->archives);}

	/*************************************************************************
	 * Passe au wiki suivant dans la liste
	 ************************************************************************/
	function getNext(){
		if(!next($this->wikis))
			return FALSE;
		return current($this->wikis);
	}

	/*************************************************************************
	 * Remet l'index a zero pour à nouveau parcourir la liste des wiki
	 ************************************************************************/
	function resetIndex(){
		reset($this->wikis);
	}

	/*************************************************************************
	 * Renvois les infos du wiki sur lequel l'index pointe
	 ************************************************************************/
	function getCur(){
		return current($this->wikis);
	}

	/*************************************************************************
	 * Supprime un wiki
	 ************************************************************************/
	function delete($name){
		//TODO : gestion des erreurs.
		$this->wikis[$name]->delete();

	}

	/*************************************************************************
	 * Crée une archive d'un wiki et renvois l'URL pour la télécharger
	 ************************************************************************/
	function save($name){
		$this->wikis[$name]->save();
	}

	function restore($name){
		$this->archives[$name]->restore();
	}

	/*************************************************************************
	 * Tri les wikis par rapport à l'une de leur caractéristique
	 ************************************************************************/
	function orderBy($tri = 'name'){
		//TODO : *
	}

	/*************************************************************************
	 * Passe au wiki suivant dans la liste
	 ************************************************************************/
	function getNextArchive(){
		if(!next($this->archives))
			return FALSE;
		return current($this->archives);
	}

	/*************************************************************************
	 * Remet l'index a zero pour a nouveau parcourir la liste des wiki
	 ************************************************************************/
	function resetIndexArchives(){
		reset($this->archives);
	}

	/*************************************************************************
	 * renvois les infos du wiki sur lequel l'index pointe
	 ************************************************************************/
	function getCurArchive(){
		return current($this->archives);
	}

	/*************************************************************************
	 * Supprime une archive
	 ************************************************************************/
	function deleteArchive($name){
		if(!isset($this->archives[$name]))
			throw new Exception("L'archive ".$name." n'existe pas.", 1);
		else
			$this->archives[$name]->delete();
	}

	/*************************************************************************
	 * Renvoi l'URL de l'interface d'administration
	 ************************************************************************/

	function getAdminURL(){
		return $this->config['base_url']."admin/";
	}

	/*************************************************************************
	 * Renvoi l'URL de la ferme
	 ************************************************************************/
	function getURL(){
		return $this->config['base_url'];
	}

	
	/*******************************************************************
	 * Securise une entrée utilisateur
	 ******************************************************************/
	private function cleanEntry($entry){
		//TODO : éliminer les caractère indésirables
		return htmlentities($entry, ENT_QUOTES, "UTF-8");
	}
	
	/*******************************************************************
	 * Détermine si un nom de wiki est valide
	 ******************************************************************/
	private function isValidWikiName($name) {
		if (preg_match("~^[a-zA-Z0-9]{1,10}$~i",$name)) {
			return false;
		}
		return true;
	}

	/*******************************************************************
	 * Installe un wiki
	 * ****************************************************************/
	function add($wikiName, $email, $description) {

		//Protection avec HashCash
		/********************************************************************/		
		// TODO : c'est le boulot du controleur ça !
		require_once('php/secret/wp-hashcash.lib');
			if(!isset($_POST["hashcash_value"]) 
				|| $_POST["hashcash_value"] != hashcash_field_value()) {
				throw new Exception("La plantation de wiki est une activité 
					délicate qui ne doit pas être effectuée par un robot. 
					(Pensez à activer JavaScript)", 1);
			}

		//Une série de tests sur les données.
		if($this->isValidWikiName($wikiName)){
			throw new Exception("Ce nom wiki n'est pas valide.", 1);
			exit();
		}

		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			throw new Exception("Cet email n'est pas valide.", 1);
			exit();
		}

		$description = $this->cleanEntry($description);

		// Fin de la partie a déplacer dans le controlleur
		/********************************************************************/

		$wiki_path = $this->config['ferme_path'].$wikiName."/";
		$package_path = "packages/".$this->config['source']."/";

		//Vérifie si le wiki n'existe pas déjà
		if (is_dir($wiki_path) || is_file($wiki_path)) {
			throw new Exception("Ce nom de wiki est déjà utilisé", 1);
			exit();
		}

		//$this->copy($this->config['source_path'], $wikiPath);
		$output = shell_exec("cp -r --preserve=mode,ownership "
			.$package_path."files"
			." ".$wiki_path);
		

		/*********************************************************************
		 * DEBUT BOUCLE DE PARCOUR DES FICHIERS DE CONFIG
		 ********************************************************************/
		include($package_path."config.php");

		foreach ($config as $file => $content) {
			file_put_contents($wiki_path.$file, utf8_encode($content));
		}
		/*********************************************************************
		 * FIN BOUCLE
		 ********************************************************************/


		//Création de la base de donnée
		$dblink = mysql_connect($this->config['db_host'], 
								$this->config['db_user'], 
								$this->config['db_password']);
		
		mysql_select_db($this->config['db_name'], 
						$dblink);
		
		include($package_path."database.php");
				
		foreach($listQuery as $query){
			$result = mysql_query($query, $dblink);
			if (!$result) {
				die('Requête invalide : ' . mysql_error());
			}
		}
		mysql_close($dblink);

		return $wiki_path;	
	}

	/*************************************************************************
	 * Retourne la liste des thèmes
	 * **********************************************************************/
	function getThemesList(){
		$themesList = array();

		foreach($this->config['themes'] as $key => $value){
			$themesList[] = $key;
		}
		return $themesList;
	}

	/*************************************************************************
	 * Vérifie les accès necessaire pour le bon fonctionnement de la ferme
	 * **********************************************************************/
	function checkInstall(){
		//TODO : 
		$is_valid = true;

		return $is_valid;
	}


}




?>
