<?php
namespace Ferme;

class ArchiveFactory {
    public function __construct($config)
    {
        $this->config = $config;
    }

    public function createFromWiki($wiki)
    {
        $archivePath = $wiki->archive();
        return $this->createFromExisting(basename($archivePath));
    }

    public function createFromExisting($filename)
    {
        $archive = new Archive($filename, $this->config);
        return $archive;
    }
}
