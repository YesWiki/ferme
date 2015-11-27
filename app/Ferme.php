<?php
namespace Ferme;

class Ferme
{
    private $config;
    private $db_connexion;
    public $wikis_factory = null;
    public $archives_factory = null;
    private $list_alerts;
    private $user_controller;

    /*************************************************************************
     * constructor
     * **********************************************************************/
    public function __construct($config)
    {
        $this->config = $config;
        $this->user_controller = new UserController($config);
        $this->wikis_factory = new WikisFactory(
            array('config' => $this->config)
        );
        $this->archives_factory = new ArchivesFactory(
            array('config' => $this->config)
        );
        $this->archives = array();
        $this->list_alerts = new Alerts();
    }

    /*************************************************************************
     * Gestion des Wikis
     ************************************************************************/
    public function loadWikis($calsize = false)
    {
        try {
            $this->wikis_factory->load($calsize);
        } catch (Exception $e) {
            // TODO : plutot envoyer un mail a l'admin.
            $this->ferme->addAlert($e->getMessage(), "error");
        }

    }

    public function countWikis()
    {
        return count($this->wikis_factory);
    }

    public function delete($name)
    {
        $this->isAuthorized();
        $this->wikis_factory->remove($name);
    }

    public function updateConfiguration($name)
    {
        $this->isAuthorized();
        $this->wikis_factory->updateConfiguration($name);
    }

    public function resetIndexWikis()
    {
        reset($this->wikis_factory);
    }

    public function getCurrentWiki()
    {
        return current($this->wikis_factory);
    }

    public function getNextWiki()
    {
        return next($this->wikis_factory);
    }

    public function searchWikis($args = '*')
    {
        return $this->wikis_factory->search($args);
    }

    public function createWiki($wikiname, $mail, $desc)
    {
        //Une série de tests sur les données.
        if ($this->isValidWikiName($wikiname)) {
            throw new Exception("Ce nom n'est pas valide.", 1);
        }

        if (!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Cet email n'est pas valide.", 1);
        }

        return $this->wikis_factory->create(
            array(
                'name' => $this->cleanEntry($wikiname),
                'mail' => $this->cleanEntry($mail),
                'desc' => $this->cleanEntry($desc),
            )
        );
    }

    /*************************************************************************
     * Gestion des archives
     ************************************************************************/
    public function archiveWiki($name)
    {
        $this->isAuthorized();
        $list_wikis = $this->wikis_factory->search($name);
        $this->archives_factory->create($list_wikis[0]);
    }

    public function loadArchives()
    {
        $this->archives_factory->load();
    }

    public function countArchives()
    {
        return count($this->archives_factory);
        return 0;
    }

    public function deleteArchive($name)
    {
        $this->isAuthorized();
        $this->archives_factory->remove($name);
    }

    public function resetIndexArchives()
    {
        reset($this->archives_factory);
    }

    public function getCurrentArchive()
    {
        return current($this->archives_factory);
    }

    public function getNextArchive()
    {
        return next($this->archives_factory);
    }

    public function searchArchives($args = '*')
    {
        return $this->archives_factory->search($args);
    }

    public function restore($name)
    {
        $this->isAuthorized();
        $list_archives = $this->archives_factory->search($name);
        $list_archives[0]->restore();
    }

    /*************************************************************************
     * Gestion des utilisateurs
     ************************************************************************/

    /**
     * Vérifie si un utilisateur est connecté
     * @return Vrai si un utilisateur est connecté, faux sinon.
     */
    public function isLogged()
    {
        return $this->user_controller->isLogged();
    }

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

    /*************************************************************************
     * Gestion des URLs
     ************************************************************************/
    /**
     * Renvoie l'URL de l'interface d'administration
     * @return string url de l'insterface d'administration
     */
    public function getAdminURL()
    {
        return $this->config['base_url'] . "?view=admin";
    }

    /**
     * Renvoie l'URL de la page d'acceuil
     * @return string url de la page d'acceuil
     */
    public function getURL()
    {
        return $this->config['base_url'];
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Retourne la liste des thèmes.
     * @return array tableau de tableau avec deux clés : name et thumb
     */
    public function getThemesList()
    {
        $themesList = array();

        include "packages/"
        . $this->config['source']
            . "/install.config.php";

        foreach ($config['themes'] as $key => $value) {
            $themesList[] = array(
                'name' => $key,
                'thumb' => $value['thumb'],
            );
        }
        return $themesList;
    }

    public function checkIntegrity()
    {

    }

    /**
     * Définis si le nom d'un wiki est valide
     * @param  strin   $name Nom potentiel du wiki.
     * @return boolean       Vrai si le nom est valide, faux sinon
     */
    private function isValidWikiName($name)
    {
        if (preg_match("~^[a-zA-Z0-9]{1,10}$~i", $name)) {
            return false;
        }
        return true;
    }

    /**
     * Nettoie une chaine de caractère
     * @param  string $entry Chaine a nettoyer
     * @return string        Chaine de caractères nettoyées
     */
    private function cleanEntry($entry)
    {
        return htmlentities($entry, ENT_QUOTES, "UTF-8");
    }
}
