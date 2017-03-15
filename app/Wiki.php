<?php
namespace Ferme;

/**
 * Classe wiki
 *
 * gère les opération sur un wiki
 * @package Ferme
 * @author  Florestan Bredow <florestan.bredow@supagro.fr>
 * @version 0.1.1 (Git: $Id$)
 * @copyright 2013 Florestan Bredow
 */
class Wiki implements InterfaceObject
{
    public $path;
    public $name;
    private $fermeConfig;
    private $dbConnexion;
    private $infos = null;
    private $config = null;

    /**
     * Constructeur
     * @param string        $path         chemin vers le wiki
     * @param Configuration $config       configuration de la ferme
     * @param PDO           $dbConnexion connexion vers la base de donnée (déjà
     * établie)
     */
    public function __construct($name, $path, $fermeConfig, $dbConnexion)
    {
        $this->name = $name;
        $this->path = $path;
        $this->fermeConfig = $fermeConfig;
        $this->dbConnexion = $dbConnexion;
        $this->loadConfiguration();
        $this->loadInfos();
    }

    public function loadConfiguration()
    {
        if (is_null($this->config)) {
            $filePath = $this->path . "wakka.config.php";
            if (!file_exists($filePath)) {
                return false;
            }
            $this->config = new Configuration($filePath);
        }
        return true;
    }

    /**
     * Calcule la taille occupée par les fichiers et la base de donnée du wiki
     * @return array Liste des informations sur le wiki avec au moins la taille
     * de la base de donnée et des fichiers
     */
    public function calSize()
    {
        if (is_null($this->infos)) {
            $this->loadInfos();
        }
        $this->infos['db_size'] = $this->calDBSize();

        $file = new \Files\File($this->path);
        $this->infos['files_size'] = $file->diskUsage();
        return $this->infos;
    }

    /**
     * Renvois les informations sur le wiki.
     *
     * @return array
     */
    public function getInfos()
    {
        if (is_null($this->infos)) {
            return $this->loadInfos();
        }
        return $this->infos;
    }

    /**
     * Supprime ce wiki.
     */
    public function delete()
    {
        $database = $this->dbConnexion;
        $fermePath = $this->fermeConfig['ferme_path'];

        //Supprime la base de donnée
        $tables = $this->getDBTablesList();

        foreach ($tables as $tableName) {
            $sth = $database->prepare("DROP TABLE IF EXISTS " . $tableName);
            if (!$sth->execute()) {
                throw new \Exception(
                    "Erreur lors de la suppression de la base de donnée",
                    1
                );
            }
        }

        //Supprimer les fichiers
        $wikiFiles = new \Files\File($fermePath . $this->config['wakka_name']);
        $wikiFiles->delete();
    }

    /**
     * Crée une archive de ce wiki.
     */
    public function archive()
    {
        $wikiName = $this->config['wakka_name'];
        $archiveFilename = $this->fermeConfig['archives_path']
            . $wikiName
            . date("YmdHi")
            . '.tgz';
        $wikiPath = realpath($this->fermeConfig['ferme_path'] . $wikiName);
        $sqlFile = $this->fermeConfig['tmp_path'] . $wikiName . '.sql';

        // Dump de la base de donnée.
        $database = new Database($this->dbConnexion);
        $database->export($sqlFile, $this->config['table_prefix']);

        // Création de l'archive
        $archive = new \PharData($archiveFilename);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $wikiPath,
                \RecursiveDirectoryIterator::SKIP_DOTS // Evite les repertoire .. et .
            )
        );

        $archive->buildFromIterator($iterator, dirname($wikiPath));
        $archive->addFile($sqlFile, basename($sqlFile));

        unset($archive);
        unlink($sqlFile);

        return $archiveFilename;
    }

    /**
     * [updateConfiguration description]
     * @return [type] [description]
     */
    public function updateConfiguration()
    {
        $this->config['mysql_host'] = $this->fermeConfig['db_host'];
        $this->config['mysql_database'] = $this->fermeConfig['db_name'];
        $this->config['mysql_user'] = $this->fermeConfig['db_user'];
        $this->config['mysql_password'] = $this->fermeConfig['db_password'];
        $this->config['base_url'] = $this->fermeConfig['base_url'];
        $this->config['base_url'] .= $this->path;
        $this->config['base_url'] .= '/wakka.php?wiki=';
        $this->config->write($this->path . "/wakka.config.php");
        return $this->config;
    }

    public function upgrade($srcPath)
    {
        // Supprime les fichiers du wiki
        $fileToIgnore = array(
            '.', '..', 'wakka.config.php', 'wakka.infos.php', 'files'
        );

        if ($res = opendir($this->path)) {
            while (($filename = readdir($res)) !== false) {
                if (!in_array($filename, $fileToIgnore)) {
                    $file = new \Files\File($this->path . '/' . $filename);
                    $file->delete();
                }
            }
            closedir($res);
        }

        // Copie les nouveaux fichiers
        if ($res = opendir($srcPath)) {
            while (($filename = readdir($res)) !== false) {
                if (!in_array($filename, $fileToIgnore)) {
                    $file = new \Files\File($srcPath . $filename);
                    $file->copy($this->path . '/' . $filename);
                }
            }
            closedir($res);
        }
    }

    /**
     * Calcul l'espace utilisé par la base de donnée.
     *
     * @return mixed
     */
    private function calDBSize()
    {
        $database = $this->dbConnexion;
        $query = "SHOW TABLE STATUS LIKE '"
        . $this->config['table_prefix']
            . "%';";

        $result = $database->query($query);
        $result->setFetchMode(\PDO::FETCH_OBJ);

        $size = 0;
        while ($row = $result->fetch()) {
            $size += $row->Data_length + $row->Index_length;
        }

        return $size;
    }

    /**
     * Récupère la liste des noms de tables dans la base de donnée pour ce Wiki.
     *
     * @param $db
     * @return mixed
     */
    private function getDBTablesList()
    {
        $database = $this->dbConnexion;
        // Echape le caractère '_' et '%'
        $search = array('%', '_');
        $replace = array('\%', '\_');
        $tablePrefix = str_replace(
            $search,
            $replace,
            $this->config['table_prefix']
        ) . '%';

        $query = "SHOW TABLES LIKE ?";
        $sth = $database->prepare($query);
        $sth->execute(array($tablePrefix));

        $results = $sth->fetchAll();

        $finalResults = array();
        foreach ($results as $value) {
            $finalResults[] = $value[0];
        }

        return $finalResults;
    }


    private function loadInfos()
    {
        unset($this->infos);

        $filePath = $this->path . "wakka.infos.php";

        $wakkaInfos = array(
            'mail' => 'nomail',
            'description' => 'Pas de description.',
            'date' => 0,
        );

        if (file_exists($filePath)) {
            include $filePath;
        }

        $this->infos = $wakkaInfos;
        $this->infos['name'] = $this->name;
        $this->infos['url'] = $this->config['base_url'];
        $this->infos['description'] = html_entity_decode(
            $this->infos['description'],
            ENT_QUOTES,
            "UTF-8"
        );

        return $this->infos;
    }

}
