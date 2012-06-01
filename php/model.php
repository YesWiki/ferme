<?php

class Farm {
	
	public $config;

	/*******************************************************************
	 * constructeur
	 * ****************************************************************/	
	function __construct($configPath) {
		include($configPath);
	}
	
	/*******************************************************************
	 * retourne la liste des wikis installés (tableau avec nom et URL)
	 * Trié par 'name', 'date', 'description', 'mail' ou 'path'
	 * ****************************************************************/
	function getWikisList($order = 'none') {
		
		$result = array();
		
		//Remplissage du tableau
		if ($handle = opendir($this->config['ferme_path'])) {
			while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
					$path = $this->config['ferme_path'].$entry;
					if(is_dir($path)){
						
						include($path."/wakka.infos.php");
						
						$result[] = array(
							'name' => $entry, 
							'path' => $path,
							'description' => $wakkaInfos['description'],
							'date' => $wakkaInfos['date'],
							'mail' => $wakkaInfos['mail'],
						);
		
						
					}
				}
			}
			closedir($handle);
		}
		
		//Tri du tableau
		if($order != 'none'){
			foreach ($result as $key => $row)
					$name[$key]  = $row[$order];
				array_multisort($name, SORT_ASC, $result);
		}

		return $result;
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
	function installWiki($wikiName, $email, $description) {

		//TODO : Protéger l'entrée $description.

		//Protection avec HashCash
		require_once('php/secret/wp-hashcash.lib');
			if(!isset($_POST["hashcash_value"]) || $_POST["hashcash_value"] != hashcash_field_value()) {
				throw new Exception("La plantation de wiki est une activité délicate qui ne doit pas être effectuée par un robot. (Pensez à activer JavaScript)", 1);
				//die("La plantation de wiki est une activité délicate qui ne doit pas être effectuée par un robot.");
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

		$description = htmlentities($description, ENT_QUOTES);

		$wikiPath = $this->config['ferme_path'].$wikiName."/";

		//Création du repertoire du nouveau wiki
		if (is_dir($wikiPath) or is_file($wikiPath)) {
			throw new Exception("Ce nom de wiki est déjà utilisé", 1);
			exit();
		}

		mkdir($wikiPath,0775,true);
			
		
		//Création des liens symboliques pour éviter la duplication des 
		//données et surtout diminuer le temps de création du wiki
		foreach($this->config['symList'] as $fname){
			$target = $this->config['source_path'].$fname;
			$link = $wikiPath.$fname;
			symlink($target, $link);
			
		}/**/
		
		//création des repertoires (ceux contenant des données 
		//spécifiques au wiki
		foreach($this->config['newDir'] as $fname){
			mkdir($wikiPath.$fname,0775);/**/
			
		}/**/
		
		//Les liens symboliques n'étant pas accepté sur les fichiers php
		//nous devons donc les copier.
		foreach($this->config['copyList'] as $fname){
			$source = $this->config['source_path'].$fname;
			$destination = $wikiPath.$fname;
			copy($source, $destination);
		}/**/
		
		$table_prefix = $wikiName."_";
		$wiki_url = $this->config['base_url'].$this->config['ferme_path'].$wikiName."/wakka.php?wiki=";
		
		include("php/writeConfig.php");
		file_put_contents($wikiPath."wakka.config.php", $configFileContent);
		
		//fichier d'infos sur le wiki
		$date = time();
		
		include("php/writeInfos.php");
		file_put_contents($wikiPath."wakka.infos.php", $infosFileContent);
		
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
