<?php
namespace Ferme;

class WikisCollection extends Collection
{
    private $config;
    private $dbConnexion = null;

    /**
     * Initialise la classe (appelé par le constructeur)
     * @param  array  args['config'] Instance de Configuration (Obligatoire)
     */
    public function __construct($config, $dbConnexion)
    {
        parent::__construct();
        $this->config = $config;
        $this->dbConnexion = $dbConnexion;
    }

    /**
     * Charge la liste des Wikis et leurs informations.
     * @param  boolean $calculate_size Si vrai calcule la taille de la base de
     * donnée et l'espace occupé sur le disque.
     */
    public function load()
    {
        $fermePath = $this->config['ferme_path'];
        $wikiFactory = new WikiFactory($this->config, $this->dbConnexion);
        $wikisList = new \RecursiveDirectoryIterator(
            $fermePath,
            \RecursiveDirectoryIterator::SKIP_DOTS
        );

        foreach ($wikisList as $wikiPath) {
            if (!is_dir($wikiPath)) {
                continue;
            }
            $wikiName = basename($wikiPath);
            $wiki = $wikiFactory->createWikiFromExisting($wikiName);
            if (!$wiki->loadConfiguration()) {
                continue;
            }
            $this->add(
                $wikiName,
                $wikiFactory->createWikiFromExisting($wikiName)
            );
        }
    }
}
