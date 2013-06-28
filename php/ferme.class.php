<?php

include_once('wiki.class.php');
include_once('archive.class.php');

class Ferme {
	
	public $config; //TODO : Must be private
	private $wikis;
	private $archives;

	/*******************************************************************
	 * constructor
	 * ****************************************************************/	
	function __construct($configPath) {
		include($configPath);
		$this->wikis = array();
		$this->archives = array();

		// TODO : Awful hack to work with Lampp (mysql binaries path is 
		// different) need a best way to give path to class Wiki and Archive
		$GLOBALS['exec_path'] = $this->config['exec_path'];
		
	}

	/*************************************************************************
	 * Load wiki list and configuration
	 ************************************************************************/
	function refresh($calsize = true){
		$this->wikis = array();
		$ferme_path = $this->config['ferme_path'];

		if ($handle = opendir($ferme_path)) {
			while (false !== ($entry = readdir($handle))) {
				$entry_path = $ferme_path.$entry;
				if ($entry != "." && $entry != ".." 
								  && is_dir($entry_path)){
					
					try {$this->wikis[$entry] = new Wiki($entry_path, $calsize);} 
					catch (Exception $e) { }//TODO : send mail to admin 
				}
			}
			closedir($handle);
		} else
			throw new Exception("Impossible d'accéder à "
								.$ferme_path, 1);
		
	}

	/*************************************************************************
	 * Load archives 's list
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
	 * Next wiki in wiki's list
	 ************************************************************************/
	function getNext(){
		if(!next($this->wikis))
			return FALSE;
		return current($this->wikis);
	}

	/*************************************************************************
	 * Reset index on wiki's list
	 ************************************************************************/
	function resetIndex(){
		reset($this->wikis);
	}

	/*************************************************************************
	 * Give current wiki information
	 ************************************************************************/
	function getCur(){
		return current($this->wikis);
	}

	/*************************************************************************
	 * delete a wiki
	 ************************************************************************/
	function delete($name){
		//TODO : gestion des erreurs.
		$this->wikis[$name]->delete();

	}

	/*************************************************************************
	 * Make wiki backup and get back url to download it
	 ************************************************************************/
	function save($name){
		$this->wikis[$name]->save();
	}

	function restore($name){
		$this->archives[$name]->restore();
	}

	/*************************************************************************
	 * Next Wiki in wiki list
	 ************************************************************************/
	function getNextArchive(){
		if(!next($this->archives))
			return FALSE;
		return current($this->archives);
	}

	/*************************************************************************
	 * Reset index on archive list
	 ************************************************************************/
	function resetIndexArchives(){
		reset($this->archives);
	}

	/*************************************************************************
	 * Get back current wiki information
	 ************************************************************************/
	function getCurArchive(){
		return current($this->archives);
	}

	/*************************************************************************
	 * Delete an archive
	 ************************************************************************/
	function deleteArchive($name){
		if(!isset($this->archives[$name]))
			throw new Exception("L'archive ".$name." n'existe pas.", 1);
		else
			$this->archives[$name]->delete();
	}

	/*************************************************************************
	 * Get back backoffice url
	 ************************************************************************/

	function getAdminURL(){
		return $this->config['base_url']."admin/";
	}

	/*************************************************************************
	 * Get back farm URL
	 ************************************************************************/
	function getURL(){
		return $this->config['base_url'];
	}

	
	/*******************************************************************
	 * Clean unwanted characters
	 ******************************************************************/
	private function cleanEntry($entry){
		//TODO : éliminer les caractère indésirables
		return htmlentities($entry, ENT_QUOTES, "UTF-8");
	}
	
	/*******************************************************************
	 * Check wikiname
	 ******************************************************************/
	private function isValidWikiName($name) {
		if (preg_match("~^[a-zA-Z0-9]{1,10}$~i",$name)) {
			return false;
		}
		return true;
	}

	/*******************************************************************
	 * Wiki installation
	 * ****************************************************************/
	function add($wikiName, $email, $description) {

		//HashCash protection
		/********************************************************************/		
		// TODO : Move it to controller !
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

		// End of part who must move to controller
		/********************************************************************/

		$wiki_path = $this->config['ferme_path'].$wikiName."/";
		$package_path = "packages/".$this->config['source']."/";


		//Vérifie si le wiki n'existe pas déjà
		if (is_dir($wiki_path) || is_file($wiki_path)) {
			throw new Exception("Ce nom de wiki est déjà utilisé", 1);
			exit();
		}

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
	 * Get back themes list
	 * **********************************************************************/
	function getThemesList(){
		$themesList = array();

		include("packages/".$this->config['source']."/install.config.php");

		foreach($config['themes'] as $key => $value){
			$themesList[] = array( 
				'name' 	 => $key,
				'thumb' => $value['thumb'],
			);
		}
		return $themesList;
	}

	/*************************************************************************
	 * Check ACL on directories
	 * **********************************************************************/
	function checkInstall(){
		//TODO : 
		$is_valid = true;

		return $is_valid;
	}


}




?>
