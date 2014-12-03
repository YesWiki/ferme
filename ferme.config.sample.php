<?php
	$this->config = array(
		'db_host' => "localhost",
		'db_name' => "XXXXXXXXXX",
		'db_user' => "XXXXXXXXXX",
		'db_password' => "XXXXXXXXXX",
		'base_url' => "http://localhost/ferme/", 
		'source' => "yeswiki-cercopitheque", 				//
		'ferme_path' => "wikis/", 				// Deprecated, do not modify
		'template' => "default",				// Template used 
		'exec_path' => "/usr/bin/", 			// Mysql binaries location (for LAMPP)
		'admin_password' => md5("password"), 	// Default md5 password for installed wikis
	);
?>

