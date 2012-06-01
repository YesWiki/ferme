<?php
	$this->config = array(
		'db_host' => "XXXXXXXXXXXXXX",
		'db_name' => "XXXXXXXXXXXXX",
		'db_user' => "XXXXXXXXXXXXX",
		'db_password' => "XXXXXXXXXX",
		'ferme_path' => "wikis/",
		'base_url' => "http://webtest.cdrflorac.fr/ferme/",
		//Necessite chemin absolu
		'source_path' => "/homez.55/cdrflora/www-webtest/ferme/wikiSource/",
		'template' => "default.phtml",
		'newDir' => array(
			'files',
			'themes',
		),
		'copyList' => array(
			'wakka.php',
			'tools.php',
			'index.php',
		),
		'symList' => array(
			'actions',
			'formatters',
			'handlers',
			'includes',
			'setup',
			'tools',
			'interwiki.conf',
			'robots.txt',
			'wakka.basic.css',
			'wakka.css',
		),
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
