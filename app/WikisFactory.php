<?php
namespace Ferme;

class WikisFactory extends Factory
{
    protected $config;
    protected $db_connexion = null;

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
    public function load($eval_size = false)
    {
        $this->dbConnect();
        $ferme_path = $this->config['ferme_path'];
        if ($handle = opendir($ferme_path)) {
            while (false !== ($wiki = readdir($handle))) {
                $wiki_path = $ferme_path . $wiki;
                if ("." != $wiki and ".." != $wiki and is_dir($wiki_path)
                ) {
                    $this->list[$wiki] = new Wiki(
                        $wiki_path,
                        $this->config,
                        $this->db_connexion
                    );
                    // Gère le cas ou le wiki a été partiellement installé.
                    if (!$this->list[$wiki]->loadConfiguration()) {
                        unset($this->list[$wiki]);
                    } else {
                        if ($eval_size) {
                            $this->list[$wiki]->calSize();
                        }
                    }
                }
            }
            closedir($handle);
        } else {
            throw new \Exception(
                "Impossible d'accéder à " . $ferme_path,
                1
            );
        }
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

        $wiki_name = $args['name'];
        $mail = $args['mail'];
        $description = $args['desc'];

        $wiki_path = $this->config['ferme_path']
            . $wiki_name
            . "/";
        $package_path = "packages/"
        . $this->config['source']
            . "/";

        // Vérifie si le wiki n'existe pas déjà
        if (is_dir($wiki_path) || is_file($wiki_path)) {
            throw new \Exception(
                "Ce nom de wiki est déjà utilisé (" . $args['name'] . ")",
                1
            );
        }

        $this->copyFiles($package_path . "files", $wiki_path);

        // TODO : Utiliser la class Configuration pour gérer cela ou pas...
        // A reflechir...
        include $package_path . "config.php";

        foreach ($config as $file => $content) {
            file_put_contents($wiki_path . $file, utf8_encode($content));
        }

        //Création de la base de donnée
        include $package_path . "database.php";

        foreach ($listQuery as $query) {
            $sth = $this->db_connexion->prepare($query);
            if (!$sth->execute()) {
                die('Requête invalide : ' . mysql_error());
            }
        }
        return $wiki_path;
    }

    /**
     * Supprime un Wiki (Fichiers et base de données)
     * @param  string $key nom du wiki a supprimer
     */
    public function remove($key)
    {
        if (isset($this->list[$key])) {
            $this->list[$key]->delete();
        } else {
            throw new \Exception(
                "Impossible de supprimer le wiki $key. Il n'existe pas.",
                1
            );
        }
    }

    public function updateConfiguration($key)
    {
        if (isset($this->list[$key])) {
            $this->list[$key]->updateConfiguration();
        } else {
            throw new \Exception(
                "Impossible de mettre à jour la configuration du wiki "
                . " $key. Il n'existe pas.",
                1
            );
        }
    }

    /**
     * Établis la connexion a la base de donnée si ce n'est pas déjà fait.
     * @return \PDO la connexion a la base de donnée
     */
    private function dbConnect()
    {
        if (!is_null($this->db_connexion)) {
            return $this->db_connexion;
        }

        $dsn = 'mysql:host='
        . $this->config['db_host']
        . ';dbname='
        . $this->config['db_name']
            . ';';

        try {
            $this->db_connexion = new \PDO(
                $dsn,
                $this->config['db_user'],
                $this->config['db_password']
            );
            return $this->db_connexion;
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
        exec($command, $output, $return_var);

        if (0 != $return_var) {
            shell_exec("rm -r " . $destination);
            throw new \Exception("Erreur lors de la copie des fichiers", 1);
        }
        return true;
    }
}
