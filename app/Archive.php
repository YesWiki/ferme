<?php
namespace Ferme;

class Archive
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
        $tab_infos['url'] = $this->getURL();
        $tab_infos['size'] = $this->calFilesSize();
        return $tab_infos;
    }

    /**
     * Génère l'URL de téléchargement d'une archive
     * @return string URL pour télécharger le fichier.
     */
    public function getURL()
    {
        $name = substr($this->filename, 0, -4);
        $url = '?action=download&archive=' . $name;
        return $url;
    }

    /**
     * Restaure une archive si le nom wiki est libre
     */
    public function restore()
    {
        $name = substr($this->filename, 0, -16);
        $ferme_path = $this->config->getParameter('ferme_path');
        $wiki_path = $ferme_path . $name . '/';
        $tmp_path = $this->config->getParameter('tmp_path');
        $archives_path = $this->config->getParameter('archives_path');
        $sql_file = $ferme_path . $name . '.sql';

        //Vérifier si le wiki n'est pas déjà existant
        if (file_exists($wiki_path)) {
            throw new \Exception('Un wiki du meme nom existe déjà.', 1);
            exit();
        }

        $output = shell_exec(
            'tar -C ' . $ferme_path
            . ' -xvzf ' . $archives_path . $this->filename
        );

        // Vérifie si les fichiers sont bien de retour au bon endroit
        if (!is_dir($wiki_path)) {
            throw new \Exception(
                'Impossible d\'extraire l\'archive',
                1
            );
            exit();
        }

        //restaurer la base de donnée
        include $wiki_path . "wakka.config.php";

        $output = shell_exec(
            'cat ' . $sql_file . ' | '
            . '/usr/bin/mysql'
            . ' --host=' . $wakkaConfig['mysql_host']
            . ' --user=' . $wakkaConfig['mysql_user']
            . ' --password=' . $wakkaConfig['mysql_password']
            . ' ' . $wakkaConfig['mysql_database']
        );

        //Effacer les fichiers temporaires

        unlink($sql_file);
    }

    /*************************************************************************
     * Supprime une archive
     ************************************************************************/
    public function delete()
    {
        if (!unlink(
            $this->config->getParameter('archives_path') . $this->filename
        )) {
            throw new \Exception('Impossible de supprimer l\'archive', 1);
            exit();
        }
    }

    /*************************************************************************
     * calcul le poid d'un fichier
     ************************************************************************/
    private function calFilesSize()
    {
        return filesize(
            $this->config->getParameter('archives_path')
            . $this->filename
        );
    }
}
