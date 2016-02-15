<?php
namespace Ferme;

class WikisFactory extends Factory
{
    protected $config;
    protected $dbConnexion = null;

    /**
     * Initialise la classe (appelé par le constructeur)
     * @param  array  args['config'] Instance de Configuration (Obligatoire)
     */
    protected function init($args = null)
    {
        if (!isset($args['config'])) {
            throw new \Exception(
                "Paramètre manquant lors de l'instantiation de "
                . get_class($this),
                1
            );
        }
        $this->config = $args['config'];
    }

    /**
     * Charge la liste des Wikis et leurs informations.
     * @param  boolean $calculate_size Si vrai calcule la taille de la base de donnée
     * et l'espace occupé sur le disque.
     */
    public function load($evalSize = false)
    {
        $this->dbConnect();
        $fermePath = $this->config['ferme_path'];
        if (!$handle = opendir($fermePath)) {
            throw new \Exception(
                "Impossible d'accéder à " . $fermePath,
                1
            );
        }
        while (false !== ($wiki = readdir($handle))) {
            $wikiPath = $fermePath . $wiki;
            if ("." != $wiki and ".." != $wiki and is_dir($wikiPath)
            ) {
                $this->list[$wiki] = new Wiki(
                    $wikiPath,
                    $this->config,
                    $this->dbConnexion
                );
                // Gère le cas ou le wiki a été partiellement installé.
                if (!$this->list[$wiki]->loadConfiguration()) {
                    unset($this->list[$wiki]);
                    continue;
                }
                if ($evalSize) {
                    $this->list[$wiki]->calSize();
                }
            }
        }
        closedir($handle);
    }

    /**
     * Installe un nouveau Wiki
     * @param string $arg['name']   Nom du nouveau wiki (Obligatoire)
     * @param string $arg['email']  Email du créateur (Obligatoire)
     * @param string $arg['desc']   Description du nouveau Wiki (Obligatoire)
     *
     * @return string chemin vers le nouveau wiki.
     */
    public function create($args = null)
    {
        $this->dbConnect();

        if (!isset($args['name'])
            or !isset($args['mail'])
            or !isset($args['desc'])
        ) {
            throw new Exception(
                "Paramètre(s) manquant lors de la création du wiki",
                1
            );
        }

        $wikiPath = $this->config['ferme_path']
            . $args['name']
            . "/";
        $packagePath = "packages/"
        . $this->config['source']
            . "/";

        // Vérifie si le wiki n'existe pas déjà
        if (is_dir($wikiPath) || is_file($wikiPath)) {
            throw new \Exception(
                "Ce nom de wiki est déjà utilisé (" . $args['name'] . ")",
                1
            );
        }

        $this->copyFiles($packagePath . "files", $wikiPath);

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
        return $wikiPath;
    }

    /**
     * Supprime un Wiki (Fichiers et base de données)
     * @param  string $key nom du wiki a supprimer
     */
    public function remove($key)
    {
        if (!isset($this->list[$key])) {
            throw new \Exception(
                "Impossible de supprimer le wiki $key. Il n'existe pas.",
                1
            );
        }
        $this->list[$key]->delete();
    }

    public function updateConfiguration($key)
    {
        if (!isset($this->list[$key])) {
            throw new \Exception(
                "Impossible de mettre à jour la configuration du wiki "
                . " $key. Il n'existe pas.",
                1
            );
        }
        $this->list[$key]->updateConfiguration();
    }

    /**
     * Établis la connexion a la base de donnée si ce n'est pas déjà fait.
     * @return \PDO la connexion a la base de donnée
     */
    private function dbConnect()
    {
        if (!is_null($this->dbConnexion)) {
            return $this->dbConnexion;
        }

        $dsn = 'mysql:host='
        . $this->config['db_host']
        . ';dbname='
        . $this->config['db_name']
            . ';';

        try {
            $this->dbConnexion = new \PDO(
                $dsn,
                $this->config['db_user'],
                $this->config['db_password']
            );
            return $this->dbConnexion;
        } catch (\PDOException $e) {
            throw new \Exception(
                "Impossible de se connecter à la base de donnée : "
                . $e->getMessage(),
                1
            );
        }
    }

    /**
     * Copie les fichiers
     * @param  string $source      Dossier source
     * @param  string $destination Dossier de destination
     * @return bool              Vrai si l'opération à réussi
     */
    private function copyFiles($source, $destination)
    {
        // TODO : trouver une solution portable et optimisée
        $output = array();
        $command = "cp -r --preserve=mode,ownership "
            . $source . " "
            . $destination;
        exec($command, $output, $returnVar);

        if (0 != $returnVar) {
            shell_exec("rm -r " . $destination);
            throw new \Exception("Erreur lors de la copie des fichiers", 1);
        }
        return true;
    }
}
