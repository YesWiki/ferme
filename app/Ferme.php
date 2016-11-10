<?php
namespace Ferme;

class Ferme
{
    public $config;
    public $wikis;
    public $archives;
    public $alerts;
    public $users;
    private $log;

    /*************************************************************************
     * constructor
     * **********************************************************************/
    public function __construct($config)
    {
        $this->config = $config;
        $this->users = new UserController($config);
        $this->wikis = new WikisCollection($config);
        $this->archives = new ArchivesCollection($config);
        $this->log = new Log($this->config['log_file']);
        $this->alerts = new Alerts();
    }

    /*************************************************************************
     * Gestion des Wikis
     ************************************************************************/
    public function delete($name)
    {
        $this->users->isAuthorized();
        $this->wikis->remove($name);
        $this->log->write(
            $this->users->whoIsLogged(),
            "Suppression du wiki '$name'"
        );
    }

    public function upgrade($name)
    {
        $this->users->isAuthorized();
        $listWikis = $this->wikis->search($name);

        $this->log->write(
            $this->users->whoIsLogged(),
            "Mise à jour du wiki '$name'"
        );

        $listWikis[0]->upgrade(
            "packages/" . $this->config['source']. "/files/"
        );
    }

    public function updateConfiguration($name)
    {
        $this->users->isAuthorized();
        $this->log->write(
            $this->users->whoIsLogged(),
            "Mise a jour de configuration de '$name'"
        );
        $this->wikis->updateConfiguration($name);
    }

    public function createWiki($wikiname, $mail, $desc)
    {
        //Une série de tests sur les données.
        if ($this->isValidWikiName($wikiname)) {
            throw new \Exception("Ce nom n'est pas valide. "
                . "(uniquement les caractères A-Z et 0-9)", 1);
        }

        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Cet email n'est pas valide.", 1);
        }

        return $this->wikis->create(
            array(
                'name' => $this->cleanEntry($wikiname),
                'mail' => $this->cleanEntry($mail),
                'desc' => $this->cleanEntry($desc),
            )
        );
    }

    public function checkInstallation()
    {
        if (!is_dir($this->config['ferme_path'])) {
            if (!mkdir($this->config['ferme_path'], 0777, true)) {
                throw new \Exception(
                    "Le dossier d'installation des wiki ("
                        . $this->config['ferme_path']
                        . ") ne peut être créé.",
                    1
                );
            }
        }

        if (!is_dir($this->config['archives_path'])) {
            if (!mkdir($this->config['archives_path'], 0777, true)) {
                throw new \Exception(
                "Le dossier des archives ("
                    . $this->config['archives_path']
                    . ") ne peut être créé.",
                    1
                );
            }
        }
    }

    /*************************************************************************
     * Gestion des archives
     ************************************************************************/
    public function archiveWiki($name)
    {
        $this->users->isAuthorized();
        $listWikis = $this->wikis->search($name);
        $this->log->write(
            $this->users->whoIsLogged(),
            "Archive le wiki '$name'"
        );
        $this->archives->create($listWikis[0]);
    }

    public function deleteArchive($name)
    {
        $this->users->isAuthorized();
        $this->log->write(
            $this->users->whoIsLogged(),
            "Suppression de l'archive '$name'"
        );
        $this->archives->remove($name);
    }

    public function restore($name)
    {
        $this->users->isAuthorized();
        $listArchives = $this->archives->search($name);
        $this->log->write(
            $this->users->whoIsLogged(),
            "Restauration de l'archive '$name'"
        );
        $listArchives[0]->restore();
    }

    /*************************************************************************
     * Gestion des URLs
     ************************************************************************/

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
