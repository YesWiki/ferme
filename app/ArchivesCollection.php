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
        $archiveFactory = new ArchiveFactory($this->config);

        $archivesList = new \RecursiveDirectoryIterator(
            $archivesPath,
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        foreach ($archivesList as $archivePath) {
            if ("tgz" === pathinfo($archivePath, PATHINFO_EXTENSION)
                and is_file($archivePath)
            ) {
                $archive = $archiveFactory->createFromExisting(
                    basename($archivePath)
                );
                $archiveName = $archive->getInfos()['filename'];
                $this->add($archiveName, $archive);
            }
        }
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
