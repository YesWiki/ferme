<?php
namespace Ferme;

use \Exception;
use \PDO;

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
    private $infos;

    /**
     * Constructeur
     */
    public function __construct($path, $calsize = true)
    {
        $this->path = $path;

        //Charge les infos sur le wiki
        $file_path = $path . "/wakka.infos.php";
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

        //Charge la configuration du wiki
        $file_path = $path . "/wakka.config.php";
        if (!file_exists($file_path)) {
            throw new Exception("Wiki mal installé (" . $path . ").", 1);
        }

        include $file_path;
        $this->config = $wakkaConfig;

        $this->infos['name'] = $this->config['wakka_name'];
        $this->infos['url'] = $this->config['base_url'];

        if ($calsize) {
            $this->infos['db_size'] = $this->calDBSize();
            $this->infos['files_size'] = $this->calFilesSize();
        }
    }

    /**
     * Renvois les informations sur le wiki.
     *
     * @return mixed
     */
    public function getInfos()
    {
        return $this->infos;
    }

    /**
     * Supprime ce wiki.
     * @todo : trouver une solution pour éviter le shell
     */
    public function delete()
    {
        //Supprime la base de donnée
        $db = $this->connectDB();
        $tables = $this->getDBTablesList($db);

        foreach ($tables as $table_name) {
            $sth = $db->prepare("DROP TABLE IF EXISTS " . $table_name);
            if (!$sth->execute()) {
                throw new Exception(
                    "Erreur lors de la suppression de la base de donnée",
                    1
                );
                exit();
            }
        }
        // Vérifie si la suppression a été effective
        $tables = $this->getDBTablesList($db);
        if (!empty($tables)) {
            throw new Exception(
                "Erreur lors de la suppression de la base de donnée",
                1
            );
            exit();
        }
        //Supprimer les fichiers
        $output = shell_exec("rm -r ../wikis/" . $this->config['wakka_name']);
        if (is_dir("../wikis/" . $this->config['wakka_name'])) {
            throw new Exception(
                "Impossible de supprimer les fichiers du wiki",
                1
            );
            exit();
        }
    }

    /**
     * Crée une archive de ce wiki.
     */
    public function save()
    {
        $name = $this->config['wakka_name'];
        $filename = "archives/" . $name . date("YmdHi") . ".tgz";

        //Création du repertoir temporaire
        $output = shell_exec("mkdir tmp/" . $name);
        if (!is_dir("tmp/" . $name)) {
            throw new \Exception(
                "Impossible de créer le repertoire temporaire"
                . " (Vérifiez les droits d'acces sur admin/tmp)",
                1
            );
            exit();
        }

        //Récupération de la base de donnée
        $this->dumpDB("tmp/" . $name . "/" . $name . ".sql");

        //Ajout des fichiers du wiki
        $output = shell_exec("cp -R ../wikis/" . $name . " tmp/" . $name . "/");
        if (!is_dir("tmp/" . $name . "/" . $name)) {
            throw new \Exception(
                "Impossible de copier les fichiers du wiki",
                1
            );
            exit();
        }

        //Compression des données
        $output = shell_exec(
            "cd tmp && tar -cvzf ../"
            . $filename . " " . $name . " && cd -"
        );

        if (!is_file($filename)) {
            throw new Exception(
                "Impossible de créer le fichier de sauvegarde"
                . " (Vérifiez les droits d'acces sur admin/archives) ",
                1
            );
            exit();
        }

        //Nettoyage des fichiers temporaires
        $output = shell_exec("rm -r tmp/" . $name);
        if (is_dir("tmp/" . $name)) {
            throw new Exception(
                "Impossible de supprimer les fichiers "
                . "temporaires. Prévenez l'administrateur.",
                1
            );
            exit();
        }
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
        $db = $this->connectDB();
        $tables = $this->getDBTablesList($db);

        $str_list_table = "";
        foreach ($tables as $table_name) {
            $str_list_table .= $table_name . " ";
        }

        $output = shell_exec(
            "mysqldump --host=" . $this->config['mysql_host']
            . " --user=" . $this->config['mysql_user']
            . " --password=" . $this->config['mysql_password']
            . " " . $this->config['mysql_database']
            . " " . $str_list_table
            . " > " . $file
        );

        return $output;
    }

    /**
     * Calcul l'espace utilisé par la base de donnée.
     *
     * @return mixed
     */
    private function calDBSize()
    {
        $db = $this->connectDB();
        $query = "SHOW TABLE STATUS LIKE '"
        . $this->config['table_prefix']
        . "%';";

        $result = $db->query($query);
        $result->setFetchMode(PDO::FETCH_OBJ);

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

        if (is_file($path)) {
            return filesize($path);
        } elseif (is_dir($path)) {
            $size = 0;
            $files = scandir($path);
            foreach ($files as $file) {
                if ("." != $file && ".." != $file) {
                    $size += $this->calFilesSize($path . "/" . $file);
                }
            }
            return $size;
        } else {
            return 0;
        }
    }

    /**
     * Connection à la base de donnée.
     *
     * @return mixed
     */
    private function connectDB()
    {
        $dsn = 'mysql:host=' . $this->config['mysql_host'] . ';'
        . 'dbname=' . $this->config['mysql_database'] . ';';

        try {
            $connexion = new PDO(
                $dsn,
                $this->config['mysql_user'],
                $this->config['mysql_password']
            );
            return $connexion;
        } catch (PDOException $e) {
            throw new \Exception(
                "Impossible de se connecter à la base de donnée : "
                . $e->getMessage(),
                1
            );
        }
    }

    /**
     * Récupère la liste des noms de tables dans la base de donnée pour ce Wiki.
     *
     * @param $db
     * @return mixed
     */
    private function getDBTablesList($db)
    {
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
