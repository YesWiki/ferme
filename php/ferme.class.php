<?php

include_once('wiki.class.php');

class Ferme {
	
	public $config; //TODO : devrait devenir privé
	private $wikis;

	/*******************************************************************
	 * constructeur
	 * ****************************************************************/	
	function __construct($configPath) {
		include($configPath);
		$this->wikis = array();
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
		}
	}

	/*************************************************************************
	 * Passe au wiki suivant dans la liste
	 ************************************************************************/
	function getNextWiki(){
		if(!next($this->wikis))
			return FALSE;
		return current($this->wikis);
	}

	/*************************************************************************
	 * Remet l'index a zero pour a nouveau parcourir la liste des wiki
	 ************************************************************************/
	function resetIndex(){
		reset($this->wikis);
	}

	/*************************************************************************
	 * renvois les infos du wiki sur lequel l'index pointe
	 ************************************************************************/
	function getCurWikiInfos(){
		return current($this->wikis)->getInfos();
	}

	/*************************************************************************
	 * Supprime un wiki
	 ************************************************************************/
	function delete($name){
		print($name);
		//TODO : gestion des erreurs.
		$this->wikis[$name]->delete();
	}

	/*************************************************************************
	 * Crée une archive d'un wiki et renvois l'URL pour la télécharger
	 ************************************************************************/
	function save($name){
		$this->wikis[$name]->save();
	}

	/*************************************************************************
	 * Tri les wikis par rapport à l'une de leur caractéristique
	 ************************************************************************/
	function orderBy($tri = 'name'){
		//TODO : *
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

		$wikiPath = $this->config['ferme_path'].$wikiName."/";

		//Vérifie si le wiki n'existe pas déjà
		if (is_dir($wikiPath) || is_file($wikiPath)) {
			throw new Exception("Ce nom de wiki est déjà utilisé", 1);
			exit();
		}

		//$this->copy($this->config['source_path'], $wikiPath);
		$output = shell_exec("cp -r --preserve=mode,ownership "
			.$this->config['source_path']
			." ".$wikiPath);
		
		$table_prefix = $wikiName."_";
		$wiki_url = $this->config['base_url']
					.$this->config['ferme_path']
					.$wikiName."/wakka.php?wiki=";
		
		include("php/writeConfig.php");
		file_put_contents($wikiPath."wakka.config.php", 
						  utf8_encode($configFileContent));
		
		//fichier d'infos sur le wiki
		$date = time();
		
		include("php/writeInfos.php");
		file_put_contents($wikiPath."wakka.infos.php", utf8_encode($infosFileContent));
		
		//Création de la base de donnée
		$dblink = mysql_connect($this->config['db_host'], 
								$this->config['db_user'], 
								$this->config['db_password']);
		
		mysql_select_db($this->config['db_name'], 
						$dblink);
		
		include("php/initDB.php");
				
		foreach($listQuery as $query){
			$result = mysql_query($query, $dblink);
			if (!$result) {
				die('Requête invalide : ' . mysql_error());
			}
		}
		mysql_close($dblink);

		return $wikiPath;	
	}

	function getThemesList(){
		$themesList = array();

		foreach($this->config['themes'] as $key => $value){
			$themesList[] = $key;
		}
		return $themesList;
	}


}




?>
