<?php
namespace Ferme;

class Ferme
{
    private $config;
    private $wikis_factory = null;
    private $archives_factory = null;
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
        $this->wikis_factory->load($calsize);
    }

    public function countWikis()
    {
        return $this->wikis_factory->count();
    }

    public function delete($name)
    {
        $this->isAuthorized();
        $this->wikis_factory->remove($name);
    }

    public function resetIndexWikis()
    {
        $this->wikis_factory->resetIndex();
    }

    public function getCurrentWiki()
    {
        return $this->wikis_factory->getCurrent();
    }

    public function getNextWiki()
    {
        return $this->wikis_factory->getNext();
    }

    public function searchWikis($args = '*')
    {
        return $this->wikis_factory->search($args);
    }

    public function archiveWiki($name)
    {
        $this->isAuthorized();
        $list_wikis = $this->wikis_factory->search($name);
        $this->archives_factory->create($list_wikis[0]);
    }

    /*************************************************************************
     * Gestion des archives
     ************************************************************************/
    public function loadArchives()
    {
        $this->archives_factory->load();
    }

    public function countArchives()
    {
        return $this->archives_factory->count();
        return 0;
    }

    public function deleteArchive($name)
    {
        $this->isAuthorized();
        $this->archives_factory->remove($name);
    }

    public function resetIndexArchives()
    {
        $this->archives_factory->resetIndex();
    }

    public function getCurrentArchive()
    {
        return $this->archives_factory->getCurrent();
    }

    public function getNextArchive()
    {
        return $this->archives_factory->getNext();
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
}
