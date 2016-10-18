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
class Wiki
{
    private $path;
    private $config;
    private $fermeConfig;
    private $dbConnexion;
    private $infos = null;

    /**
     * Constructeur
     * @param string        $path         chemin vers le wiki
     * @param Configuration $config       configuration de la ferme
     * @param PDO           $dbConnexion connexion vers la base de donnée (déjà
     * établie)
     */
    public function __construct($path, $config, $dbConnexion)
    {
        $this->path = $path;
        $this->fermeConfig = $config;
        $this->dbConnexion = $dbConnexion;
    }

    public function loadConfiguration()
    {
        $filePath = $this->path . "/wakka.config.php";
        if (!file_exists($filePath)) {
            return false;
        }
        $this->config = new Configuration($filePath);
        return $this->config;
    }

    private function loadInfos()
    {
        unset($this->infos);

        $filePath = $this->path . "/wakka.infos.php";

        $wakkaInfos = array(
            'mail' => 'nomail',
            'description' => 'Pas de description.',
            'date' => 0,
        );

        if (file_exists($filePath)) {
            include $filePath;
        }

        $this->infos = $wakkaInfos;
        $this->infos['name'] = $this->config['wakka_name'];
        $this->infos['url'] = $this->config['base_url'];
        $this->infos['description'] = html_entity_decode(
            $this->infos['description'],
            ENT_QUOTES,
            "UTF-8"
        );

        return $this->infos;
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
        $archiveFileName = $this->fermeConfig['archives_path']
            . $wikiName
            . date("YmdHi")
            . '.tgz';
        $wikiPath = $this->fermeConfig['ferme_path'] . $wikiName;
        $sqlFile = $this->fermeConfig['tmp_path'] . $wikiName . '.sql';

        // Dump de la base de donnée.
        $database = new Database($this->dbConnexion);
        $database->export($sqlFile, $this->config['table_prefix']);

        $directory = new \RecursiveDirectoryIterator(realpath($wikiPath));
        $iterator = new \RecursiveIteratorIterator($directory);

        // Regenère l'itérateur pour définir le chemin interne à l'archive
        $fileList = array();
        foreach ($iterator as $key => $value) {
            if (!in_array($value->getFilename(), array('..', '.'))) {
                $archiveInternalPath = substr(
                    $value,
                    strlen(realpath($this->fermeConfig['ferme_path'])) + 1
                );
                $fileList[$archiveInternalPath] = $key;
            }
        }
        $obj = new \ArrayObject($fileList);

        // Création de l'archive
        $archive = new \PharData($archiveFileName);
        $archive->buildFromIterator($obj->getIterator());
        $archive->addFile($sqlFile, basename($sqlFile));

        unset($archive);
        unlink($sqlFile);

        return $archiveFileName;
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
}
