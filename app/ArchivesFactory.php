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
        $archives_path = $this->config['archives_path'];

        if ($handle = opendir($archives_path)) {
            while (false !== ($archive = readdir($handle))) {
                $archive_path = $archives_path . $archive;
                if ("." != $archive && ".." != $archive && is_file($archive_path)
                ) {
                    $this->list[$archive] = new Archive($archive, $this->config);
                }
            }
            closedir($handle);
        } else {
            throw new \Exception("Impossible d'accéder à " . $archives_path, 1);
        }
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
        if (isset($this->list[$key])) {
            $this->list[$key]->delete();
        } else {
            throw new \Exception(
                "Impossible de supprimer l'archive $key. Il n'existe pas.",
                1
            );
        }
    }
}
