<?php
/****************************************************************************
 * Clé -> chemin du fichier (relatif a l'emplacement d'installation)
 * Valeur -> Contenu du fichier
 ***************************************************************************/

$table_prefix = $wikiName . "_";
$wiki_url = $this->config->getParameter('base_url')
. $this->config->getParameter('ferme_path')
. $wikiName . "/wakka.php?wiki=";
$WikiAdminPasswordMD5 = $this->config->getParameter('admin_password');
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
	'mysql_host' => '" . $this->config->getParameter('db_host') . "',
	'mysql_database' => '" . $this->config->getParameter('db_name') . "',
	'mysql_user' => '" . $this->config->getParameter('db_user') . "',
	'mysql_password' => '" . $this->config->getParameter('db_password') . "',
	'table_prefix' => '$table_prefix',
	'base_url' => '$wiki_url',
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
);
?>",

    'wakka.infos.php' =>
    "<?php\n"
    . "\t\$wakkaInfos = array (\n"
    . "\t\t'mail' => '$email',\n"
    . "\t\t'description' => '$description',\n"
    . "\t\t'date' => '$date',\n"
    . "\t\t'version' => 'cercopitheque',\n"
    . "\t);\n"
    . "?>",
);
