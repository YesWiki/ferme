<?php
namespace Ferme;

use Exception;

class Archive
{
    private $filename;
    
    /*************************************************************************
     * Constructeur
     ************************************************************************/
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /*************************************************************************
     * retournes les infos sur l'archive
     ************************************************************************/
    public function getInfos()
    {
        $tab_infos['name'] = substr($this->filename, 0, -16);
        $tab_infos['filename'] = $this->filename;
        $str_date = substr($this->filename, -16, 12);
        $tab_infos['date'] = mktime(
            intval(substr($str_date, 8, 2)),
            intval(substr($str_date, 10, 2)),
            0,
            intval(substr($str_date, 4, 2)),
            intval(substr($str_date, 6, 2)),
            intval(substr($str_date, 0, 4))
        );
        $tab_infos['url']  = '../admin/archives/'.$this->filename;

        $tab_infos['size'] = $this->calFilesSize();

        return $tab_infos;
    }

    /*************************************************************************
     * Restaure une archive si le nom wiki est libre
     ************************************************************************/
    public function restore()
    {
        $name = substr($this->filename, 0, -16);
        $path = "../wikis/".$name;
        
        //Vérifier si le wiki n'est pas déjà existant
        if (file_exists($path)) {
            throw new Exception("Le wiki existe déjà.", 1);
            exit();
        }

        //Décompresser les données
        $output = shell_exec("mkdir tmp/".$name);
        if (!is_dir("tmp/".$name)) {
            throw new Exception(
                "Impossible de créer le repertoire temporaire"
                ." (Vérifiez les droits d'acces sur admin/tmp)",
                1
            );
            exit();
        }

        $output = shell_exec(
            "cd tmp && tar -xvzf ../archives/"
            .$this->filename." && cd -"
        );

        if (!is_dir("tmp/".$name)) {
            throw new Exception(
                "Impossible d'extraire l'archive (Vérifiez "
                ."les droits d'acces sur admin/tmp) ",
                1
            );
            exit();
        }

        //déplacer les fichiers
        $output = shell_exec("mv tmp/".$name."/".$name." ../wikis/");
        if (!is_dir("../wikis/".$name)) {
            throw new Exception(
                "Impossible de replacer les fichiers du wiki "
                ."(Vérifiez les droits d'acces sur wikis/) ",
                1
            );
            exit();
        }

        //restaurer la base de donnée
        include("../wikis/".$name."/wakka.config.php");

        $output = shell_exec(
            "cat tmp/".$name."/".$name.".sql | "
            ."/usr/bin/mysql"
            ." --host=".$wakkaConfig['mysql_host']
            ." --user=".$wakkaConfig['mysql_user']
            ." --password=".$wakkaConfig['mysql_password']
            ." ".$wakkaConfig['mysql_database']
        );

        //Effacer les fichiers temporaires
        $output = shell_exec("rm -r tmp/".$name);
        if (is_dir("tmp/".$name)) {
            throw new Exception(
                "Impossible de supprimer les fichiers "
                ."temporaires. Prévenez l'administrateur.",
                1
            );
            exit();
        }

    }

    /*************************************************************************
     * Supprime une archive
     ************************************************************************/
    public function delete()
    {
        $output = shell_exec("rm ../admin/archives/".$this->filename);
        if (is_file("../admin/archives/".$this->filename)) {
            throw new Exception("Impossible de supprimer l'archive", 1);
            exit();
        }
    }

    private function calFilesSize()
    {
        return filesize("../admin/archives/".$this->filename);
    }
}
