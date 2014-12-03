<?php

class Wiki{

	private $path;
	private $config;
	private $infos;

	/*************************************************************************
	 * Constructor
	 ************************************************************************/
	function __construct($path, $calsize = true) {
		$this->path = $path;

		//Charge les infos sur le wiki
		$file_path = $path."/wakka.infos.php";
		if(file_exists($file_path))
			include($file_path);
		else 
			$wakkaInfos = array (
				'mail' => 'nomail',
				'description' => 'Pas de description.',
				'date' => 0,
			);
		$this->infos = $wakkaInfos;

		//Charge la configuration du wiki
		$file_path = $path."/wakka.config.php";
		if(!file_exists($file_path))
			throw new Exception("Wiki mal installé (".$path.").", 1);
			
		include($file_path);
		$this->config = $wakkaConfig;

		$this->infos['name'] = $this->config['wakka_name'];
		$this->infos['url'] = $this->config['base_url'];

		if($calsize){
			$this->infos['db_size'] = $this->calDBSize();
			$this->infos['files_size'] = $this->calFilesSize();
		}
	}

	/*************************************************************************
	 * Connect to database
	 ************************************************************************/
	private function connectDB(){
			$mysqli = mysqli_connect(
	    	$this->config['mysql_host'], 
	    	$this->config['mysql_user'], 
	    	$this->config['mysql_password'],
	    	$this->config['mysql_database'] );

	    	return $mysqli;
	}

	/*************************************************************************
	 * get table list for wiki
	 ************************************************************************/
	private function getDBTablesList($mysqli){
		return mysqli_query($mysqli,
	    	"SHOW TABLES LIKE '".$this->config['table_prefix']."\_%'");
	}


	/*************************************************************************
	 * Get back wiki informations
	 ************************************************************************/
	function getInfos(){ return $this->infos;}

	/*************************************************************************
	 * delete this wiki
	 ************************************************************************/
	function delete(){ 

		//Supprimer les fichiers
		$output = shell_exec("rm -r ../wikis/".$this->config['wakka_name']);
		if(is_dir("../wikis/".$this->config['wakka_name'])) {
			throw new Exception("Impossible de supprimer les fichiers du wiki", 1);
			exit();
		}

		$mysqli = $this->connectDB();
		$tables = $this->getDBTablesList($mysqli);

		while($table = mysqli_fetch_array($tables)) {

			if(!mysqli_query($mysqli,"DROP TABLE ".$table[0])){
				throw new Exception("Impossible de supprimer la table "
					.$table[0]." dans la base de donnée", 1);
				exit();
			}
		}

		mysqli_close($mysqli);
	}

	/*************************************************************************
	 * make an archive of this wiki
	 ************************************************************************/
	function save(){
		$name = $this->config['wakka_name'];
		$filename = "archives/".$name.date("YmdHi").".tgz";

		//Création du repertoir temporaire
		$output = shell_exec("mkdir tmp/".$name);
		if(!is_dir("tmp/".$name)) {
			throw new Exception("Impossible de créer le repertoire temporaire"
				." (Vérifiez les droits d'acces sur admin/tmp)", 1);
			exit();
		}

		//Récupération de la base de donnée
		//$dump = $this->dumpDB();
		//file_put_contents("tmp/".$name."/".$name.".sql", $dump);
		$this->dumpDB("tmp/".$name."/".$name.".sql");

	    
		//Ajout des fichiers du wiki
		$output = shell_exec("cp -R ../wikis/".$name." tmp/".$name."/");
		if(!is_dir("tmp/".$name."/".$name)) {
			throw new Exception("Impossible de copier les fichiers du wiki", 1);
			exit();
		}
		
		//Compression des données
		$output = shell_exec("cd tmp && tar -cvzf ../".$filename." ".$name
						    ." && cd -");
		if(!is_file($filename)) {
			throw new Exception("Impossible de créer le fichier de sauvegarde"
				." (Vérifiez les droits d'acces sur admin/archives) ", 1);
			exit();
		}
		
		//Nettoyage des fichiers temporaires
		$output = shell_exec("rm -r tmp/".$name);
		if(is_dir("tmp/".$name)) {
			throw new Exception("Impossible de supprimer les fichiers "
				."temporaires. Prévenez l'administrateur.", 1);
			exit();
		}	

	}

		/*************************************************************************
	 * SQL Dump of database (this wiki only)
	 ************************************************************************/
	function dumpDB($file){

		$mysqli = $this->connectDB();
	    $tables = $this->getDBTablesList($mysqli);

	    $str_list_table = "";
	    while($table = mysqli_fetch_array($tables))
	    {
	    	$str_list_table .= $table[0]." ";
	    }

	    $output = shell_exec($GLOBALS['exec_path']
	    					."mysqldump --host=".$this->config['mysql_host']
	    					." --user=".$this->config['mysql_user']
	    					." --password=".$this->config['mysql_password']
	    					." ".$this->config['mysql_database']
	    					." ".$str_list_table
	    					." > ".$file);

	    return $output;
	}

	/*************************************************************************
	 * Calculate database usage
	 ************************************************************************/
	private function calDBSize(){

		$dns = 'mysql:host='.$this->config['mysql_host'].';'
			  .'dbname='.$this->config['mysql_database'].';';
			  //.'port=3606';

		$connexion = new PDO($dns, 
						 $this->config['mysql_user'], 
						 $this->config['mysql_password']);

		$query = "SHOW TABLE STATUS LIKE '"
				.$this->config['table_prefix']."%';";

		$result = $connexion->query($query);

		$result->setFetchMode(PDO::FETCH_OBJ);

		$size = 0;
		while($row = $result->fetch()){
			$size += $row->Data_length + $row->Index_length;
		}
		
		return $size;
	}

	/*************************************************************************
	 * calculate recursively disk space.
	 ************************************************************************/
	private function calFilesSize($path = ""){

		if ($path == "")
			$path = $this->path;

		if (is_file($path)){

			return filesize($path);
		}
		else if (is_dir($path)) {
			$size = 0;
			$files = scandir($path);
			foreach ($files as $file) {
				if($file != "." && $file != ".."){
					$size += $this->calFilesSize($path."/".$file); 
				}
			}
			return $size;
		} 
		else return 0;
	}
}			

	


?>

