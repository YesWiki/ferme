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
    private $ferme_config;
    private $db_connexion;
    private $infos = null;

    /**
     * Constructeur
     * @param string        $path         chemin vers le wiki
     * @param Configuration $config       configuration de la ferme
     * @param PDO           $db_connexion connexion vers la base de donnée (déjà
     * établie)
     */
    public function __construct($path, $config, $db_connexion)
    {
        $this->path = $path;
        $this->ferme_config = $config;
        $this->db_connexion = $db_connexion;
    }

    public function loadConfiguration()
    {
        $file_path = $this->path . "/wakka.config.php";
        if (!file_exists($file_path)) {
            return false;
        }
        $this->config = new Configuration($file_path);
        return $this->config;
    }

    private function loadInfos()
    {
        unset($this->infos);

        $file_path = $this->path . "/wakka.infos.php";
        if (file_exists($file_path)) {
            include $file_path;
        } else {
            $wakkaInfos = array(
                'mail' => 'nomail',
                'description' => 'Pas de description.',
                'date' => 0,
            );
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
        $db = $this->db_connexion;
        $tables = $this->getDBTablesList($db);

        $ferme_path = $this->ferme_config['ferme_path'];

        foreach ($tables as $table_name) {
            $sth = $db->prepare("DROP TABLE IF EXISTS " . $table_name);
            if (!$sth->execute()) {
                throw new \Exception(
                    "Erreur lors de la suppression de la base de donnée",
                    1
                );
                exit();
            }
        }
        // Vérifie si la suppression a été effective
        $tables = $this->getDBTablesList($db);
        if (!empty($tables)) {
            throw new \Exception(
                "Erreur lors de la suppression de la base de donnée",
                1
            );
            exit();
        }
        //Supprimer les fichiers
        $output = shell_exec(
            'rm -r ' . $ferme_path . $this->config['wakka_name']
        );
        if (is_dir($ferme_path . $this->config['wakka_name'])) {
            throw new \Exception(
                "Impossible de supprimer les fichiers du wiki",
                1
            );
            exit();
        }
    }

    /**
     * Crée une archive de ce wiki.
     */
    public function archive()
    {
        $wiki_name = $this->config['wakka_name'];
        $filename = $this->ferme_config['archives_path']
        . $wiki_name . date("YmdHi") . '.tgz';
        $ferme_path = $this->ferme_config['ferme_path'];
        $tmp_path = $this->ferme_config['tmp_path'];

        // Dump de la base de donnée.
        $sql_file = $this->dumpDB($tmp_path . $wiki_name . '.sql');

        // TODO : Solution portable est optimisée
        $cmd = 'tar -czf ' . $filename
        . ' -C ' . $ferme_path . ' ' . $wiki_name
        . ' -C  ' . realpath($tmp_path) . ' ' . $wiki_name . '.sql';

        shell_exec($cmd);

        unlink($sql_file);

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
        $db = $this->db_connexion;
        $tables = $this->getDBTablesList($db);

        $str_list_table = "";
        foreach ($tables as $table_name) {
            $str_list_table .= $table_name . " ";
        }
        $output = shell_exec(
            "mysqldump --host=" . $this->ferme_config['db_host']
            . " --user=" . $this->ferme_config['db_user']
            . " --password=" . $this->ferme_config['db_password']
            . " " . $this->ferme_config['db_name']
            . " " . $str_list_table
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
        $db = $this->db_connexion;
        $query = "SHOW TABLE STATUS LIKE '"
        . $this->config['table_prefix']
            . "%';";

        $result = $db->query($query);
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
        $this->config['mysql_host'] = $this->ferme_config['db_host'];
        $this->config['mysql_database'] = $this->ferme_config['db_name'];
        $this->config['mysql_user'] = $this->ferme_config['db_user'];
        $this->config['mysql_password'] = $this->ferme_config['db_password'];
        $this->config['base_url'] = $this->ferme_config['base_url'];
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
        $db = $this->db_connexion;
        // Echape le caractère '_' et '%'
        $search = array('%', '_');
        $replace = array('\%', '\_');
        $table_prefix = str_replace(
            $search,
            $replace,
            $this->config['table_prefix']
        ) . '%';

        $query = "SHOW TABLES LIKE ?";
        $sth = $db->prepare($query);
        $sth->execute(array($table_prefix));

        $results = $sth->fetchAll();

        $final_results = array();
        foreach ($results as $value) {
            $final_results[] = $value[0];
        }

        return $final_results;
    }
}
