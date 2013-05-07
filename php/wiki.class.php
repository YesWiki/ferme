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

		$this->infos['name'] = $this->config['wakka_name'];
		$this->infos['url'] = $this->config['base_url'];

		$this->infos['db_size'] = $this->calDBSize();
		$this->infos['files_size'] = $this->calFilesSize();
	}

	/*************************************************************************
	 * Retourne les infos sur le wiki
	 ************************************************************************/
	function getInfos(){ return $this->infos;}


	/*************************************************************************
	 * Supprime le wiki
	 ************************************************************************/
	function delete(){ 

		//Supprimer les fichiers
		$output = shell_exec("rm -r ../wikis/".$this->config['wakka_name']);
		if(is_dir("../wikis/".$this->config['wakka_name'])) {
			throw new Exception("Impossible de supprimer les fichiers du wiki", 1);
			exit();
		}

		//Supprimer la base de donnée
		$mysqli = mysqli_connect(
	    	$this->config['mysql_host'], 
	    	$this->config['mysql_user'], 
	    	$this->config['mysql_password'],
	    	$this->config['mysql_database'] );

		//On récupère la liste des tables du wiki
		$tables = $tables = mysqli_query($mysqli,
	    	"SHOW TABLES LIKE '".$this->config['table_prefix']."%'");


		while($table = mysqli_fetch_array($tables)) {

			if(!mysqli_query($mysqli,"DROP TABLE ".$table[0])){
				throw new Exception("Impossible de supprimer la table ".$table[0]." dans la base de donnée", 1);
				exit();
			}
		}

		mysqli_close($mysqli);
	}

	/*************************************************************************
	 * Créé une archive du Wiki
	 ************************************************************************/
	function save(){
		$name = $this->config['wakka_name'];
		$filename = "archives/".$name.date("YmdHi").".tgz";

		//Création du repertoir temporaire
		$output = shell_exec("mkdir tmp/".$name);
		if(!is_dir("tmp/".$name)) {
			throw new Exception("Impossible de créer le repertoire temporaire (Vérifiez les droits d'acces sur admin/tmp)", 1);
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
			throw new Exception("Impossible de créer le fichier de sauvegarde (Vérifiez les droits d'acces sur admin/archives) ", 1);
			exit();
		}
		
		//Nettoyage des fichiers temporaires
		$output = shell_exec("rm -r tmp/".$name);
		if(is_dir("tmp/".$name)) {
			throw new Exception("Impossible de supprimer les fichiers temporaires. Prévenez l'administrateur.", 1);
			exit();
		}	

	}

	/*************************************************************************
	 * Exporte la base de donnée en SQL
	 ************************************************************************/
	/*function dumpDB(){
	    $mysqli = mysqli_connect(
	    	$this->config['mysql_host'], 
	    	$this->config['mysql_user'], 
	    	$this->config['mysql_password'],
	    	$this->config['mysql_database'] );
	 
	    $create = "";
	    $insert = "";
	 
	 	//Charge la liste des tables du wiki
	    $tables = mysqli_query($mysqli,
	    	"SHOW TABLES LIKE '".$this->config['table_prefix']."%'");

	    while($table = mysqli_fetch_array($tables))
	    {

            //Structure de la table
            $listeCreationsTables = mysqli_query($mysqli, "show create table ".$table[0]);
            while($creationTable = mysqli_fetch_array($listeCreationsTables))
            {
              $create .= $creationTable[1].";\n\n";
            }

			//Données            
            $data = mysqli_query($mysqli, "SELECT * FROM ".$table[0]);
            while($nuplet = mysqli_fetch_array($data))
            {
                $insert .= "INSERT INTO ".$table[0]." VALUES(";
                for($i=0; $i < mysqli_num_fields($data); $i++)
                {
                  if($i != 0)
                     $insert .=  ", ";
                  if(mysqli_fetch_field_direct($data, $i) == "string" || mysqli_fetch_field_direct($data, $i) == "blob")
                     $insert .=  "'";
                  $insert .= addslashes($nuplet[$i]);
                  if(mysqli_fetch_field_direct($data, $i) == "string" || mysqli_fetch_field_direct($data, $i) == "blob")
                    $insert .=  "'";
                }
                $insert .=  ");\n";
            }
            $insert .= "\n";
        }
	 
	    mysqli_close($mysqli);

	    return $create."\n\n".$insert;
	}*/

	function dumpDB($file){

		//On récupère la liste des tables
		$mysqli = mysqli_connect(
	    	$this->config['mysql_host'], 
	    	$this->config['mysql_user'], 
	    	$this->config['mysql_password'],
	    	$this->config['mysql_database'] );
	 
	    
	 	//Charge la liste des tables du wiki
	    $tables = mysqli_query($mysqli,
	    	"SHOW TABLES LIKE '".$this->config['table_prefix']."%'");

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

	private function calDBSize(){

		$dns = 'mysql:host='.$this->config['mysql_host'].';'
			  .'dbname='.$this->config['mysql_database'].';'
			  .'port=3606';

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

	private function calFilesSize(){
	
		$output = shell_exec("du -s ../wikis/".$this->infos["name"]);
		$size = explode("\t", $output);
				
		return intval($size[0]);

	}
}


?>

