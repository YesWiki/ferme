<?php
namespace Ferme;

class Wikifactory
{
    private $fermeConfig;
    private $dbConnexion;

    public function __construct($fermeConfig, $dbConnexion)
    {
        $this->fermeConfig = $fermeConfig;
        $this->dbConnexion = $dbConnexion;
    }

    /**
     * Charge un wiki déjà installé
     * @param  string $name nom du wiki a charger.
     * @return  Wiki        Le wiki chargé.
     */
    public function createWikiFromExisting($name)
    {
        $wikiPath = $this->getWikiPath($name);
        $wiki = new Wiki($wikiPath, $this->fermeConfig, $this->dbConnexion);
        return $wiki;
    }

    /**
     * Install un nouveau wiki
     * @param  string $name        Nom du wiki
     * @param  string $mail        Mail de la personne qui install le wiki
     * @param  string $description Description du Wiki
     * @return Wiki                Le wiki fraichement installé
     */
    public function createNewWiki($name, $mail, $description)
    {
        $this->installNewWiki($name, $mail, $description);
        return $this->createWikiFromExisting($name);
    }

    public function createFromArchive($archive)
    {
        $wikiName = $this->installFromArchive($archive);
        return $this->createWikiFromExisting($wikiName);
    }

    private function getWikiPath($name)
    {
        return $this->fermeConfig['ferme_path'] . $name . "/";
    }

    private function installNewWiki($wikiName, $mail, $description)
    {
        $wikiPath = $this->getWikiPath($wikiName);
        $packagePath = "packages/"
            . $this->fermeConfig['source']
            . "/";

        // Vérifie si le wiki n'existe pas déjà
        if (is_dir($wikiPath) || is_file($wikiPath)) {
            throw new \Exception(
                "Ce nom de wiki est déjà utilisé (" . $wikiName . ")",
                1
            );
        }

        $wikiSrcFiles = new \Files\File($packagePath . "files");
        $wikiSrcFiles->copy($wikiPath);

        // TODO : Utiliser la class Configuration pour gérer cela ou pas...
        // A reflechir...
        include $packagePath . "config.php";

        foreach ($config as $file => $content) {
            file_put_contents($wikiPath . $file, utf8_encode($content));
        }

        //Création de la base de donnée
        include $packagePath . "database.php";

        foreach ($listQuery as $query) {
            $sth = $this->dbConnexion->prepare($query);
            if (!$sth->execute()) {
                throw new \Exception(
                    "Erreur lors de la création de la base de donnée.",
                    1
                );
            }
        }
    }

    /**
     * install un wiki a partir d'une archive
     */
    private function installFromArchive($archive)
    {
        $name = substr($archive->filename, 0, -16);
        $fermePath = realpath($this->fermeConfig['ferme_path']);
        $sqlFile = $fermePath . '/' . $name . '.sql';

        $archivePhar = new \PharData(
            $this->fermeConfig['archives_path'] . $archive->filename
        );
        $archivePhar->extractTo($fermePath);

        $database = new Database($this->dbConnexion);
        $database->import($sqlFile);

        unlink($sqlFile);
        return $name;
    }
}
