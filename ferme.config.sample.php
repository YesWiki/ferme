<?php
	$this->config = array(
		'db_host' => "localhost",
		'db_name' => "XXXXXXXXXX",
		'db_user' => "XXXXXXXXXX",
		'db_password' => "XXXXXXXXXX",
		'base_url' => "http://localhost/ferme/",
		'source' => "yeswiki0.2",
		'ferme_path' => "wikis/",
		'template' => "default",
		'themes' => array(
			'YesWiki + colonne à gauche' => array(
				'theme' => 'yeswiki',
				'style' => 'green.css',
				'squelette' => 'responsive-2cols-left.tpl.html',
				'thumb' => 'img/responsive-2cols-left.png',
			),
			'YesWiki mono colonne' => array(
				'theme' => 'yeswiki',
				'style' => 'green.css',
				'squelette' => 'responsive-1col.tpl.html',
				'thumb' => 'img/responsive-1col.png',
			),
			'YesWiki + colonne à droite' => array(
				'theme' => 'yeswiki',
				'style' => 'green.css',
				'squelette' => 'responsive-2cols-right.tpl.html',
				'thumb' => 'img/responsive-2cols-right.png',
			),
			'YesWiki + colonne de chaque coté' => array(
				'theme' => 'yeswiki',
				'style' => 'green.css',
				'squelette' => 'responsive-3cols.tpl.html',
				'thumb' => 'img/responsive-3cols.png',
			),
		),
	);
?>

