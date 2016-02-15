<?php
namespace Ferme;

class ArchivesFactory extends Factory
{
    protected $config;

    protected function init($args = null)
    {
        if (!isset($args['config'])) {
            throw new Exception(
                "Paramètre manquant lors de l'instantiation de "
                . get_class($this),
                1
            );
        }
        $this->config = $args['config'];
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

    /**
     * Créé une archive
     * @param  Wiki $args['wiki'] Instance de Wiki a archiver.
     * @return [type]       [description]
     */
    public function create($args = null)
    {
        if (!($args instanceof Wiki)) {
            throw new \Exception(
                "Impossible de créer une archive parametres incorrecte.",
                1
            );
        }
        $args->archive();
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
    }
}
