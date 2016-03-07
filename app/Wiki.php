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
        $this->infos['files_size'] = $this->calFilesSize();
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
     * @todo : trouver une solution pour éviter le shell
     */
    public function delete()
    {
        //Supprime la base de donnée
        $database = $this->dbConnexion;
        $tables = $this->getDBTablesList($this->dbConnexion);

        $fermePath = $this->fermeConfig['ferme_path'];

        foreach ($tables as $tableName) {
            $sth = $database->prepare("DROP TABLE IF EXISTS " . $tableName);
            if (!$sth->execute()) {
                throw new \Exception(
                    "Erreur lors de la suppression de la base de donnée",
                    1
                );
            }
        }
        // Vérifie si la suppression a été effective
        $tables = $this->getDBTablesList($this->dbConnexion);
        if (!empty($tables)) {
            throw new \Exception(
                "Erreur lors de la suppression de la base de donnée",
                1
            );
        }
        //Supprimer les fichiers
        shell_exec(
            'rm -r ' . $fermePath . $this->config['wakka_name']
        );
        if (is_dir($fermePath . $this->config['wakka_name'])) {
            throw new \Exception(
                "Impossible de supprimer les fichiers du wiki",
                1
            );
        }
    }

    /**
     * Crée une archive de ce wiki.
     */
    public function archive()
    {
        $wikiName = $this->config['wakka_name'];
        $filename = $this->fermeConfig['archives_path']
        . $wikiName . date("YmdHi") . '.tgz';
        $fermePath = $this->fermeConfig['ferme_path'];
        $tmpPath = $this->fermeConfig['tmp_path'];

        // Dump de la base de donnée.
        $sqlFile = $this->dumpDB($tmpPath . $wikiName . '.sql');

        // TODO : Solution portable et optimisée
        $cmd = 'tar -czf ' . $filename
        . ' -C ' . $fermePath . ' ' . $wikiName
        . ' -C  ' . realpath($tmpPath) . ' ' . $wikiName . '.sql';

        $output = array();
        exec($cmd, $output, $returnVar);

        unlink($sqlFile);

        if (0 != $returnVar) {
            throw new \Exception("Erreur lors de la création de l'archive.", 1);
        }
        return $filename;
    }

    /**
     * Dump la base de donnée
     * @todo : Trouver une solution PHP only
     *
     * @param $file
     * @return mixed
     */
    private function dumpDB($file)
    {
        $database = $this->dbConnexion;
        $tables = $this->getDBTablesList($database);

        $strListTable = "";
        foreach ($tables as $tableName) {
            $strListTable .= $tableName . " ";
        }
        shell_exec(
            "mysqldump --host=" . $this->fermeConfig['db_host']
            . " --user=" . $this->fermeConfig['db_user']
            . " --password=" . $this->fermeConfig['db_password']
            . " " . $this->fermeConfig['db_name']
            . " " . $strListTable
            . " > " . $file
        );

        return $file;
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
     * Cacul l'espace disque utilisé.
     *
     * @param $path
     * @return int
     */
    private function calFilesSize($path = "")
    {
        if ("" == $path) {
            $path = $this->path;
        }

        if (is_file($path) or is_dir($path)) {
            $output = shell_exec('du -s ' . $path);
            $size = intval(explode("\t", $output)[0]);
            // Résultat en octet
            return $size * 1024;
        }

        return 0;
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
