<?php
namespace Ferme;

class ArchivesCollection extends Collection
{
    private $config;

    public function __construct($config)
    {
        parent::__construct();
        $this->config = $config;
    }

    public function load()
    {
        $this->list = array();
        $archivesPath = $this->config['archives_path'];

        if (!$handle = opendir($archivesPath)) {
            throw new \Exception("Impossible d'accéder à " . $archivesPath, 1);
        }
        while (false !== ($archive = readdir($handle))) {
            $archivePath = $archivesPath . $archive;
            if ("." != $archive
                and ".." != $archive
                and "tgz" === pathinfo($archive, PATHINFO_EXTENSION)
                and is_file($archivePath)
            ) {
                $this->list[$archive] = new Archive($archive, $this->config);
            }
        }
        closedir($handle);
    }

    public function remove($key)
    {
        if (!isset($this->list[$key])) {
            throw new \Exception(
                "Impossible de supprimer l'archive $key. Il n'existe pas.",
                1
            );
        }
        $this->list[$key]->delete();
        unset($this->list[$key]);
    }
}
