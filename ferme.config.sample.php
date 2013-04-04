<?php
	$this->config = array(
		'db_host' => "XXXXXXXXXXXXXX",
		'db_name' => "XXXXXXXXXXXXX",
		'db_user' => "XXXXXXXXXXXXX",
		'db_password' => "XXXXXXXXXX",
		'base_url' => "http://localhost/ferme/",
		'ferme_path' => "wikis/",
		'source_path' => "wikiSource/",
		'template' => "default.phtml",
		'themes' => array(
			'SupAgro' => array(
				'theme' => 'yeswiki',
				'style' => 'yeswiki-green.css',
				'squelette' => 'yeswiki.tpl.html',
				'thumb' => 'img/YesWiki1.png',
			),
			'SupAgro + menu à gauche' => array(
				'theme' => 'yeswiki',
				'style' => 'yeswiki-green.css',
				'squelette' => 'yeswiki-2cols-left.tpl.html',
				'thumb' => 'img/YesWiki2.png',
			),
			'YesWiki' => array(
				'theme' => 'yeswiki',
				'style' => 'yeswiki.css',
				'squelette' => 'yeswiki.tpl.html',
				'thumb' => 'img/YesWiki3.png',
			),

		),
	);
?>
