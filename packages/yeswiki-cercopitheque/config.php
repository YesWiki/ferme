<?php
/****************************************************************************
 * Clé -> chemin du fichier (relatif a l'emplacement d'installation)
 * Valeur -> Contenu du fichier
 ***************************************************************************/

$tablePrefix = $wikiName . "_";
$wikiUrl = $this->fermeConfig['base_url']
    . $this->fermeConfig['ferme_path']
    . $wikiName . "/wakka.php?wiki=";
$WikiAdminPasswordMD5 = $this->fermeConfig['admin_password'];
$date = time();

// Take theme list from package
include "install.config.php";
$theme = $config['themes'];

$config = array(
    'wakka.config.php' =>
    "<?php \n\$wakkaConfig = array (
	'wakka_version' => '0.1.1',
	'wikini_version' => '0.5.0',
	'yeswiki_version' => 'Cercopitheque',
  	'yeswiki_release' => '2014.11.24',
	'debug' => 'no',
	'mysql_host' => '" . $this->fermeConfig['db_host'] . "',
	'mysql_database' => '" . $this->fermeConfig['db_name'] . "',
	'mysql_user' => '" . $this->fermeConfig['db_user'] . "',
	'mysql_password' => '" . $this->fermeConfig['db_password'] . "',
	'table_prefix' => '$tablePrefix',
	'base_url' => '$wikiUrl',
	'rewrite_mode' => '0',
	'meta_keywords' => 'MotCleDuSite',
	'meta_description' => '$wikiName',
	'action_path' => 'actions',
	'handler_path' => 'handlers',
	'header_action' => 'header',
	'footer_action' => 'footer',
	'navigation_links' => 'DerniersChangements :: DerniersCommentaires :: ParametresUtilisateur',
	'referrers_purge_time' => 24,
	'pages_purge_time' => 90,
	'default_write_acl' => '*',
	'default_read_acl' => '*',
	'default_comment_acl' => '@admins',
	'preview_before_save' => '0',
	'allow_raw_html' => '1',
	'root_page' => 'PagePrincipale',
	'wakka_name' => '$wikiName',
	'default_language' => 'fr',
	'favorite_theme' => '" . $theme[$_POST['theme']]['theme'] . "',
	'favorite_style' => '" . $theme[$_POST['theme']]['style'] . "',
	'favorite_squelette' => '" . $theme[$_POST['theme']]['squelette'] . "',
);",

    'wakka.infos.php' =>
    "<?php\n"
    . "\t\$wakkaInfos = array (\n"
    . "\t\t'mail' => '$mail',\n"
    . "\t\t'description' => '$description',\n"
    . "\t\t'date' => '$date',\n"
    . "\t\t'version' => 'cercopitheque',\n"
    . "\t);\n"
    . "?>",
);
