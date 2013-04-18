<?php

class Wiki{

	private $path;
	private $config;
	private $infos;

	/*************************************************************************
	 * Constructeur
	 ************************************************************************/
	function __construct($path) {
		$this->path = $path;

		//Charge les infos sur le wiki
		include($path."/wakka.infos.php");
		$this->infos = $wakkaInfos;

		//Charge la configuration du wiki
		include($path."/wakka.config.php");
		$this->config = $wakkaConfig;
	}

	/*************************************************************************
	 * Retourne les infos sur le wiki
	 ************************************************************************/
	function getInfos(){
		$tab_infos = $this->infos;
		$tab_infos['name'] = $this->config['wakka_name'];
		$tab_infos['url'] = $this->config['base_url'];
		return $tab_infos;
	}

	function delete(){
		//throw new Exception("Wiki->delete() : TODO", 1);
	}

	function save(){
		$name = $this->config['wakka_name'];

		//Création du repertoir temporaire
		$output = shell_exec("mkdir tmp/".$name);
		if(!is_dir("tmp/".$name)) {
			throw new Exception("Impossible de créer le repertoire temporaire", 1);
			exit();
		}
			


	}

	function rename($newName){
		throw new Exception("Wiki->rename() : TODO", 1);
	}
}


?>

