<?php
namespace Ferme;

class Archive implements InterfaceObject
{
    /**
     * @var mixed
     */
    private $filename;
    /**
     * @var mixed
     */
    private $config;

    /**
     * Constructeur
     *
     * @param string $filename chemin vers le fichier d'archive
     * @param Configuration $config Classe gérant le configuration
     */
    public function __construct($filename, $config)
    {
        $this->filename = $filename;
        $this->config = $config;
    }

    /**
     * retournes les infos sur l'archive
     *
     * @return array
     */
    public function getInfos()
    {
        $tabInfos['name'] = substr($this->filename, 0, -16);
        $tabInfos['filename'] = $this->filename;
        $strDate = substr($this->filename, -16, 12);
        $tabInfos['date'] = mktime(
            intval(substr($strDate, 8, 2)),
            intval(substr($strDate, 10, 2)),
            0,
            intval(substr($strDate, 4, 2)),
            intval(substr($strDate, 6, 2)),
            intval(substr($strDate, 0, 4))
        );
        $tabInfos['url'] = $this->getURL();
        $tabInfos['size'] = $this->calFilesSize();
        return $tabInfos;
    }

    /**
     * Génère l'URL de téléchargement d'une archive
     * @return string URL pour télécharger le fichier.
     */
    public function getURL()
    {
        $name = substr($this->filename, 0, -4);
        $url = '?download=' . $name;
        return $url;
    }

    /**
     * Restaure une archive si le nom wiki est libre
     */
    public function restore()
    {
        $name = substr($this->filename, 0, -16);
        $fermePath = realpath($this->config['ferme_path']);
        //$wikiPath = $fermePath . $name . '/';
        $archivesFile = realpath($this->config['archives_path'] . $this->filename);
        $sqlFile = $fermePath . '/' . $name . '.sql';

        $archive = new \PharData($archivesFile);
        $archive->extractTo($fermePath);

        $database = new Database($this->dbConnect());
        $database->import($sqlFile);

        unlink($sqlFile);
    }

    /*************************************************************************
     * Supprime une archive
     ************************************************************************/
    public function delete()
    {
        if (!unlink(
            $this->config['archives_path'] . $this->filename
        )) {
            throw new \Exception('Impossible de supprimer l\'archive', 1);
        }
    }

    /*************************************************************************
     * calcul le poid d'un fichier
     ************************************************************************/
    private function calFilesSize()
    {
        return filesize(
            $this->config['archives_path']
            . $this->filename
        );
    }

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
