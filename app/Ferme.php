<?php
namespace Ferme;

class Ferme
{
    private $config;
    public $wikisFactory = null;
    public $archivesFactory = null;
    private $listAlerts;
    private $userController;
    private $log;

    /*************************************************************************
     * constructor
     * **********************************************************************/
    public function __construct($config)
    {
        $this->config = $config;
        $this->userController = new UserController($config);
        $this->wikisFactory = new WikisFactory($config);
        $this->archivesFactory = new ArchivesFactory($config);
        $this->log = new Log($this->config['log_file']);
        $this->listAlerts = new Alerts();
    }

    /*************************************************************************
     * Gestion des Wikis
     ************************************************************************/
    public function loadWikis($calsize = false)
    {
        try {
            $this->wikisFactory->load($calsize);
        } catch (Exception $e) {
            // TODO : plutot envoyer un mail à l'admin.
            $this->ferme->addAlert($e->getMessage(), "error");
        }
    }

    public function countWikis()
    {
        return count($this->wikisFactory);
    }

    public function delete($name)
    {
        $this->isAuthorized();
        $this->log->write($this->whoIsLogged(), "Suppression du wiki '$name'");
        $this->wikisFactory->remove($name);

    }

    public function updateConfiguration($name)
    {
        $this->isAuthorized();
        $this->log->write(
            $this->whoIsLogged(),
            "Mise a jour de configuration de '$name'"
        );
        $this->wikisFactory->updateConfiguration($name);
    }

    public function resetIndexWikis()
    {
        reset($this->wikisFactory);
    }

    public function getCurrentWiki()
    {
        return current($this->wikisFactory);
    }

    public function getNextWiki()
    {
        return next($this->wikisFactory);
    }

    public function searchWikis($args = '*')
    {
        return $this->wikisFactory->search($args);
    }

    public function createWiki($wikiname, $mail, $desc)
    {
        //Une série de tests sur les données.
        if ($this->isValidWikiName($wikiname)) {
            throw new Exception("Ce nom n'est pas valide.", 1);
        }

        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Cet email n'est pas valide.", 1);
        }

        return $this->wikisFactory->create(
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
        $listWikis = $this->wikisFactory->search($name);
        $this->log->write(
            $this->whoIsLogged(),
            "Archive le wiki '$name'"
        );
        $this->archivesFactory->create($listWikis[0]);
    }

    public function loadArchives()
    {
        $this->archivesFactory->load();
    }

    public function countArchives()
    {
        return count($this->archivesFactory);
        return 0;
    }

    public function deleteArchive($name)
    {
        $this->isAuthorized();
        $this->log->write(
            $this->whoIsLogged(),
            "Suppression de l'archive '$name'"
        );
        $this->archivesFactory->remove($name);
    }

    public function resetIndexArchives()
    {
        reset($this->archivesFactory);
    }

    public function getCurrentArchive()
    {
        return current($this->archivesFactory);
    }

    public function getNextArchive()
    {
        return next($this->archivesFactory);
    }

    public function searchArchives($args = '*')
    {
        return $this->archivesFactory->search($args);
    }

    public function restore($name)
    {
        $this->isAuthorized();
        $listArchives = $this->archivesFactory->search($name);
        $this->log->write(
            $this->whoIsLogged(),
            "Restauration de l'archive '$name'"
        );
        $listArchives[0]->restore();
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
        return $this->userController->isLogged();
    }

    public function login($username, $password)
    {
        return $this->userController->login($username, $password);
    }

    public function logout()
    {
        $this->userController->logout();
    }

    public function whoIsLogged()
    {
        return $this->userController->whoIsLogged();
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
        $this->listAlerts->add($text, $type);
    }

    public function getListAlerts()
    {
        $listAlerts = $this->listAlerts->getAll();
        $this->listAlerts->removeAll();
        return $listAlerts;
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
