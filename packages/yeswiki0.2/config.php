<?php
/****************************************************************************
 * Clé -> chemin du fichier (relatif a l'emplacement d'installation)
 * Valeur -> Contenu du fichier
 ***************************************************************************/

$table_prefix = $wikiName."_";
$wiki_url = $this->config['base_url']
			.$this->config['ferme_path']
			.$wikiName."/wakka.php?wiki=";
$WikiAdminPasswordMD5 = $this->config['admin_password'];
$date = time();

$config = array(

	'wakka.config.php' => 
"<?php \$wakkaConfig = array (
	'wakka_version' => '0.1.1',
	'wikini_version' => '0.5.0',
	'debug' => 'no',
	'mysql_host' => '".$this->config['db_host']."',
	'mysql_database' => '".$this->config['db_name']."',
	'mysql_user' => '".$this->config['db_user']."',
	'mysql_password' => '".$this->config['db_password']."',
	'table_prefix' => '$table_prefix',
	'root_page' => 'PagePrincipale',
	'wakka_name' => '$wikiName',
	'base_url' => '$wiki_url',
	'rewrite_mode' => '0',
	'meta_keywords' => '',
	'meta_description' => '',
	'action_path' => 'actions',
	'handler_path' => 'handlers',
	'header_action' => 'header',
	'footer_action' => 'footer',
	'navigation_links' => 'DerniersChangements :: DerniersCommentaires :: ParametresUtilisateur',
	'referrers_purge_time' => 24,
	'pages_purge_time' => 90,
	'default_write_acl' => '*',
	'default_read_acl' => '*',
	'default_comment_acl' => '*',
	'preview_before_save' => '0',
	'allow_raw_html' => '1',
	'favorite_theme' => '".$this->config['themes'][$_POST['theme']]['theme']."',
	'favorite_style' => '".$this->config['themes'][$_POST['theme']]['style']."',
	'favorite_squelette' => '".$this->config['themes'][$_POST['theme']]['squelette']."',
);
?>",
	'wakka.infos.php' => "<?php"
						."\t\$wakkaInfos = array (\n"
						."\t\t'mail' => '$email',\n"
						."\t\t'description' => '$description',\n"
						."\t\t'date' => '$date',\n"
						."\t);\n"
						."?>",

);?>
