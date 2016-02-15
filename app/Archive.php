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
        $fermePath = $this->config['ferme_path'];
        $wikiPath = $fermePath . $name . '/';
        $archivesPath = $this->config['archives_path'];
        $sqlFile = $fermePath . $name . '.sql';

        //Vérifier si le wiki n'est pas déjà existant
        if (file_exists($wikiPath)) {
            throw new \Exception('Un wiki du meme nom existe déjà.', 1);
        }

        $output = shell_exec(
            'tar -C ' . $fermePath
            . ' -xvzf ' . $archivesPath . $this->filename
        );

        // Vérifie si les fichiers sont bien de retour au bon endroit
        if (!is_dir($wikiPath)) {
            throw new \Exception(
                'Impossible d\'extraire l\'archive',
                1
            );
        }

        //restaurer la base de donnée
        include $wikiPath . "wakka.config.php";

        $output = shell_exec(
            'cat ' . $sqlFile . ' | '
            . '/usr/bin/mysql'
            . ' --host=' . $wakkaConfig['mysql_host']
            . ' --user=' . $wakkaConfig['mysql_user']
            . ' --password=' . $wakkaConfig['mysql_password']
            . ' ' . $wakkaConfig['mysql_database']
        );

        //Effacer les fichiers temporaires

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
}
