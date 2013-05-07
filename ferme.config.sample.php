<?php
	$this->config = array(
		'db_host' => "localhost",
		'db_name' => "XXXXXXXXXX",
		'db_user' => "XXXXXXXXXX",
		'db_password' => "XXXXXXXXXX",
		'base_url' => "http://localhost/ferme/",
		'source' => "yeswiki0.2",
		'ferme_path' => "wikis/", //parametres qui va disparaitre, ne pas changer.
		'template' => "default",
		'exec_path' => "/usr/bin/", //Ou trouver les executables mysql (utile pour lampp)
		'themes' => array(
			'YesWiki + colonne à gauche' => array(
				'theme' => 'yeswiki',
				'style' => 'green.css',
				'squelette' => 'responsive-2cols-left.tpl.html',
				'thumb' => 'packages/yeswiki0.2/thumbs/responsive-2cols-left-green.png',
			),
			'YesWiki mono colonne' => array(
				'theme' => 'yeswiki',
				'style' => 'green.css',
				'squelette' => 'responsive-1col.tpl.html',
				'thumb' => 'packages/yeswiki0.2/thumbs/responsive-1col-green.png',
			),
			'YesWiki + colonne à droite' => array(
				'theme' => 'yeswiki',
				'style' => 'green.css',
				'squelette' => 'responsive-2cols-right.tpl.html',
				'thumb' => 'packages/yeswiki0.2/thumbs/responsive-2cols-right-green.png',
			),
			'YesWiki + colonne de chaque coté' => array(
				'theme' => 'yeswiki',
				'style' => 'green.css',
				'squelette' => 'responsive-3cols.tpl.html',
				'thumb' => 'packages/yeswiki0.2/thumbs/responsive-3cols-green.png',
			),
			'YesWiki + colonne à gauche' => array(
				'theme' => 'yeswiki',
				'style' => 'blue.css',
				'squelette' => 'responsive-2cols-left.tpl.html',
				'thumb' => 'packages/yeswiki0.2/thumbs/responsive-2cols-left-blue.png',
			),
			'YesWiki mono colonne' => array(
				'theme' => 'yeswiki',
				'style' => 'blue.css',
				'squelette' => 'responsive-1col.tpl.html',
				'thumb' => 'packages/yeswiki0.2/thumbs/responsive-1col-blue.png',
			),
			'YesWiki + colonne à droite' => array(
				'theme' => 'yeswiki',
				'style' => 'blue.css',
				'squelette' => 'responsive-2cols-right.tpl.html',
				'thumb' => 'packages/yeswiki0.2/thumbs/responsive-2cols-right-blue.png',
			),
			'YesWiki + colonne de chaque coté' => array(
				'theme' => 'yeswiki',
				'style' => 'blue.css',
				'squelette' => 'responsive-3cols.tpl.html',
				'thumb' => 'packages/yeswiki0.2/thumbs/responsive-3cols-blue.png',
			),
		),
	);
?>

