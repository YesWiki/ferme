<?php

class updateLN {

	public $config;

	function __construct($configPath) {
		include($configPath);
	}

	function killAllLN(){
		$ferme_path = "../".$this->config['ferme_path'];
		if ($handle = opendir($ferme_path)) {
			while (false !== ($entry = readdir($handle))) {
				$entry_path = $ferme_path.$entry;
				if ($entry != "." && $entry != ".." 
								  && is_dir($entry_path)){
					$this->purgeWiki($entry_path);
				}
			}
			closedir($handle);
		}
	}

	function purgeWiki($path){
		$source_path = $this->config['source_path'];
		if ($handle = opendir($path)) {
			while (false !== ($entry = readdir($handle))) {
				$entry_path = $path."/".$entry;

				if ($entry != "." && $entry != ".." 
								  && is_link($entry_path)){
					//shell_exec("rm ".$entry_path);
					//shell_exec("cp -R ".$source_path.$entry." ".$path."/");
					print($entry_path."<br />");
				}
			}
			closedir($handle);
		}
	}
}


$updateln = new updateLN("../ferme.config.php");

$updateln->killAllLN();

print "OK";



?>
