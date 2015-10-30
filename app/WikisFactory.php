<?php
namespace Ferme;

class WikisFactory extends Factory
{
    protected $config;

    /**
     * Initialise la classe (appelé par le constructeur)
     * @param  array  args['config'] Instance de Configuration (Obligatoire)
     */
    protected function init($args = null)
    {
        if (!isset($args['config'])) {
            throw new Exception(
                "Paramètre manquant lors de l'instantiation de "
                . get_class($this),
                1
            );
        }
        $this->config = $args['config'];
    }

    /**
     * Charge la liste des Wikis et leurs informations.
     * @param  boolean $calsize Si vrai calcule la taille de la base de donnée
     * et l'espace occupé sur le disque.
     */
    public function load($calsize = false)
    {
        $ferme_path = $this->config->getParameter('ferme_path');
        if ($handle = opendir($ferme_path)) {
            while (false !== ($wiki = readdir($handle))) {
                $wiki_path = $ferme_path . $wiki;
                if ("." != $wiki && ".." != $wiki && is_dir($wiki_path)
                ) {
                    $this->list[$wiki] = new Wiki(
                        $wiki_path,
                        $this->config,
                        $calsize
                    );
                }
            }
            closedir($handle);
        } else {
            throw new \Exception(
                "Impossible d'accéder à " . $this->ferme_path,
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

        $wiki_path = $this->config->getParameter('ferme_path')
            . $wiki_name
            . "/";
        $package_path = "packages/"
        . $this->config->getParameter('source')
            . "/";

        // Vérifie si le wiki n'existe pas déjà
        if (is_dir($wiki_path) || is_file($wiki_path)) {
            throw new \Exception("Ce nom de wiki est déjà utilisé", 1);
            exit();
        }

        // TODO : trouver une solution portable et optimisée
        $output = shell_exec(
            "cp -r --preserve=mode,ownership "
            . $package_path
            . "files"
            . " " . $wiki_path
        );

        // TODO : Utiliser la class Configuration pour gérer cela ou pas...
        // A reflechir...
        include $package_path . "config.php";

        foreach ($config as $file => $content) {
            file_put_contents($wiki_path . $file, utf8_encode($content));
        }

        //TODO : PDO
        //Création de la base de donnée
        $dblink = mysql_connect(
            $this->config->getParameter('db_host'),
            $this->config->getParameter('db_user'),
            $this->config->getParameter('db_password')
        );

        mysql_select_db(
            $this->config->getParameter('db_name'),
            $dblink
        );

        include $package_path . "database.php";

        foreach ($listQuery as $query) {
            $result = mysql_query($query, $dblink);
            if (!$result) {
                die('Requête invalide : ' . mysql_error());
            }
        }
        mysql_close($dblink);

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
}
