<?php

include_once('wiki.class.php');

class Ferme{
	
	private $config;
	private $wikis;

	/*************************************************************************
	 * Constructeur
	 ************************************************************************/
	function __construct($configPath) {
		include($configPath);
		$this->loadFerme();
	}

	/*************************************************************************
	 * Charge la liste des wikis et leur config
	 ************************************************************************/
	function loadFerme(){
		$this->wikis = array();

		$ferme_path = "../".$this->config['ferme_path'];
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
		$this->loadFerme();
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

	}

	/*************************************************************************
	 * Renvoi l'URL de l'interface d'administration
	 ************************************************************************/

	function getURL(){
		return $this->config['base_url']."admin/";
	}
}


?>