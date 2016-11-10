<?php
namespace Ferme;

class Ferme
{
    public $config;
    public $wikis;
    public $archives;
    public $alerts;
    public $users;
    public $dbConnexion;
    private $log;

    /*************************************************************************
     * constructor
     * **********************************************************************/
    public function __construct($config)
    {
        $this->config = $config;
        $this->dbConnect();
        $this->users = new UserController($config);
        $this->wikis = new WikisCollection($config, $this->dbConnexion);
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
        $this->wikis->delete($name);
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
        $wikisFinded = $this->wikis->search($name);
        $wiki = $wikisFinded[0];
        $wiki->updateConfiguration();
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
        $archiveName = $listWikis[0]->archive();
        $archive = new Archive(basename($archiveName), $this->config);

        $archiveName = $archive->getInfos()['filename'];
        // TODO définir le nom de l'archive ici.
        $this->archives->add($archiveName, $archive);
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
     * Établis la connexion a la base de donnée si ce n'est pas déjà fait.
     * @return \PDO la connexion a la base de donnée
     */
    private function dbConnect()
    {
        $dsn = 'mysql:host=' . $this->config['db_host'] . ';';
        $dsn .= 'dbname=' . $this->config['db_name'] . ';';

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
}
