<?php
namespace Ferme;

class Download
{
    private $name;
    private $path;

    public function __construct($name, $ferme)
    {
        $this->name = $name . '.tgz';
        $this->path = $ferme->config['archives_path'];

        if (!file_exists($this->path . $this->name)) {
            throw new \Exception("Le fichier n'existe pas.", 1);
        }
    }

    public function serve()
    {
        $file = $this->path . $this->name;
        header('Content-type: application/tgz');
        header('Content-Disposition: inline; filename="' . $this->name . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        return;
    }
}
