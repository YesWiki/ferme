<?php
namespace Ferme;

class Ferme
{

    private $config; //TODO : Must be private
    private $wikis;
    private $archives;
    private $list_alerts;
    private $user_controller;

    /*************************************************************************
     * constructor
     * **********************************************************************/
    public function __construct($config)
    {
        $this->config = $config;
        $this->user_controller = new UserController($config);
        $this->wikis = array();
        $this->archives = array();
        $this->list_alerts = new Alerts();
    }

    /*************************************************************************
     * Load wiki list and configuration
     ************************************************************************/
    public function refresh($calsize = true)
    {
        $this->wikis = array();
        $ferme_path = $this->config->getParameter('ferme_path');

        if ($handle = opendir($ferme_path)) {
            while (false !== ($entry = readdir($handle))) {
                $entry_path = $ferme_path . $entry;
                if ("." != $entry && ".." != $entry && is_dir($entry_path)
                ) {
                    $this->wikis[$entry] = new Wiki(
                        $entry_path,
                        $this->config,
                        $calsize
                    );
                }
            }
            closedir($handle);
        } else {
            throw new \Exception("Impossible d'accéder à " . $ferme_path, 1);
        }
    }

    /*************************************************************************
     * Load archives 's list
     ************************************************************************/
    public function refreshArchives()
    {
        $this->archives = array();
        $archives_path = $this->config->getParameter('archives_path');

        if ($handle = opendir($archives_path)) {
            while (false !== ($entry = readdir($handle))) {
                $entry_path = $archives_path . $entry;
                if ("." != $entry && ".." != $entry && is_file($entry_path)
                ) {
                    $this->archives[$entry] = new Archive($entry, $this->config);
                }
            }
            closedir($handle);
        } else {
            throw new \Exception("Impossible d'accéder à " . $archives_path, 1);
        }
    }

    public function nbWikis()
    {
        return count($this->wikis);
    }

    public function nbArchives()
    {
        return count($this->archives);
    }

    public function getConfig()
    {
        return $this->config;
    }

    /*************************************************************************
     * Next wiki in wiki's list
     ************************************************************************/
    public function getNext()
    {
        if (!next($this->wikis)) {
            return false;
        }
        return current($this->wikis);
    }

    /*************************************************************************
     * Reset index on wiki's list
     ************************************************************************/
    public function resetIndex()
    {
        reset($this->wikis);
    }

    /*************************************************************************
     * Give current wiki information
     ************************************************************************/
    public function getCur()
    {
        return current($this->wikis);
    }

    /*************************************************************************
     * delete a wiki
     ************************************************************************/
    public function delete($name)
    {
        $this->isAuthorized();
        //TODO : gestion des erreurs.
        if (isset($this->wikis[$name])) {
            $this->wikis[$name]->delete();
        } else {
            throw new \Exception(
                "Impossible de supprimer le wiki $name. Il n'existe pas.",
                1
            );
        }
    }

    /*************************************************************************
     * Make wiki backup and get back url to download it
     ************************************************************************/
    public function save($name)
    {
        $this->isAuthorized();
        $this->wikis[$name]->save($this->config->getParameter('archives_path'));
    }

    /*************************************************************************
     * Make wiki backup and get back url to download it
     ************************************************************************/
    public function restore($name)
    {
        $this->isAuthorized();
        $this->archives[$name]->restore();
    }

    /*************************************************************************
     * Next Wiki in wiki list
     ************************************************************************/
    public function getNextArchive()
    {
        if (!next($this->archives)) {
            return false;
        }
        return current($this->archives);
    }

    /*************************************************************************
     * Reset index on archive list
     ************************************************************************/
    public function resetIndexArchives()
    {
        reset($this->archives);
    }

    /**
     * Renvoi
     * @return [type] [description]
     */
    public function getCurArchive()
    {
        return current($this->archives);
    }

    /**
     * Supprime une archive
     * @param  string $name nom de l'archive a supprimer
     */
    public function deleteArchive($name)
    {
        if (!isset($this->archives[$name])) {
            throw new \Exception("L'archive " . $name . " n'existe pas.", 1);
        } else {
            $this->archives[$name]->delete();
        }
    }

    /**
     * Renvoie l'URL de l'interface d'administration
     * @return string url de l'insterface d'administration
     */
    public function getAdminURL()
    {
        return $this->config->getParameter('base_url') . "?view=admin";
    }

    /**
     * Renvoie l'URL de la page d'acceuil
     * @return string url de la page d'acceuil
     */
    public function getURL()
    {
        return $this->config->getParameter('base_url');
    }

    /**
     * Nettoie une chaine de caractère
     * @param  string $entry Chaine a nettoyer
     * @return string        Chaine de caractères nettoyées
     */
    private function cleanEntry($entry)
    {
        //TODO : éliminer les caractère indésirables
        return htmlentities($entry, ENT_QUOTES, "UTF-8");
    }

    /**
     * Installe un nouveau Wiki
     * @param string $wikiName    Nom du nouveau wiki
     * @param string $email       Email du créateur
     * @param string $description Description du nouveau Wiki
     *
     * @return string chemin vers le nouveau wiki.
     */
    public function add($wikiName, $email, $description)
    {

        $description = $this->cleanEntry($description);

        $ferme_path = $this->config->getParameter('ferme_path');
        $wiki_path = $ferme_path . $wikiName . "/";
        $package_path = "packages/"
        . $this->config->getParameter('source')
            . "/";

        //Vérifie si le wiki n'existe pas déjà
        if (is_dir($wiki_path) || is_file($wiki_path)) {
            throw new \Exception("Ce nom de wiki est déjà utilisé", 1);
            exit();
        }

        $output = shell_exec(
            "cp -r --preserve=mode,ownership "
            . $package_path . "files"
            . " " . $wiki_path
        );

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
     * Retourne la liste des thèmes.
     * @return array tableau de tableau avec deux clés : name et thumb
     */
    public function getThemesList()
    {
        $themesList = array();

        include "packages/"
        . $this->config->getParameter('source')
            . "/install.config.php";

        foreach ($config['themes'] as $key => $value) {
            $themesList[] = array(
                'name' => $key,
                'thumb' => $value['thumb'],
            );
        }
        return $themesList;
    }

    /**
     * Vérifie si un utilisateur est connecté
     * @return Vrai si un utilisateur est connecté, faux sinon.
     */
    public function isLogged()
    {
        return $this->user_controller->isLogged();
    }

    /*************************************************************************
     * Gestion des utilisateurs
     ************************************************************************/
    public function login($username, $password)
    {
        return $this->user_controller->login($username, $password);
    }

    public function logout()
    {
        $this->user_controller->logout();
    }

    public function whoIsLogged()
    {
        return $this->user_controller->whoIsLogged();
    }

    private function isAuthorized()
    {
        if (!$this->isLogged()) {
            throw new \Exception("Accès interdit", 1);
        }
    }

    /*************************************************************************
     * Gestion des alertes
     ************************************************************************/
    public function addAlert($text, $type = "notice")
    {
        $this->list_alerts->add($text, $type);
    }

    public function getListAlerts()
    {
        $list_alerts = $this->list_alerts->getAll();
        $this->list_alerts->removeAll();
        return $list_alerts;
    }
}
