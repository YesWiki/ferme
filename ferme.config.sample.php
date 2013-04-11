<?php
	$this->config = array(
		'db_host' => "localhost",
		'db_name' => "XXXXXXXXXX",
		'db_user' => "XXXXXXXXXX",
		'db_password' => "XXXXXXXXXX",
		'base_url' => "http://localhost/ferme/",
		'source_path' => "wikiSource/",
		'ferme_path' => "wikis/",
		'template' => "default.phtml",
		'themes' => array(
			'YesWiki + colonne à gauche' => array(
				'theme' => 'yeswiki',
				'style' => 'green.css',
				'squelette' => 'fullscreen-2cols-left.tpl.html',
				'thumb' => 'img/YesWiki2.png',
			),
			'YesWiki mono colonne' => array(
				'theme' => 'yeswiki',
				'style' => 'green.css',
				'squelette' => 'responsive-1col.tpl.html',
				'thumb' => 'img/YesWiki1.png',
			),
			'YesWiki + colonne à droite' => array(
				'theme' => 'yeswiki',
				'style' => 'green.css',
				'squelette' => 'responsive-2cols-right.tpl.html',
				'thumb' => 'img/YesWiki3.png',
			),
			'YesWiki + colonne de chaque coté' => array(
				'theme' => 'yeswiki',
				'style' => 'green.css',
				'squelette' => 'responsive-3cols.tpl.html',
				'thumb' => 'img/YesWiki3.png',
			),
		),
	);
?>

