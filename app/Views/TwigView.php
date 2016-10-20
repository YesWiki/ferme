<?php
namespace Ferme\Views;

abstract class TwigView extends View
{
    protected $twig;

    /**
     * Rassemble les informations necessaire pour la vue.
     * @return none
     */
    abstract protected function compileInfos();

    protected function getTemplate()
    {
        $explodedClassName = explode('\\', get_class($this));
        $className = end($explodedClassName);
        return "$className.twig";
    }

    public function __construct($ferme)
    {
        parent::__construct($ferme);
        $twigLoader = new \Twig_Loader_Filesystem($this->getThemePath());
        $this->twig = new \Twig_Environment($twigLoader);
    }

    public function show()
    {
        $listInfos = $this->compileInfos();
        $listInfos = $this->addThemesInfos($listInfos);
        $listInfos = $this->addUserInfos($listInfos);
        echo $this->twig->render($this->getTemplate(), $listInfos);
    }

    /**
     * Ajoute les informations sur le thème courant (js, css...)
     * @param [type] &$infos le tableau d'information a completer.
     */
    private function addThemesInfos(&$infos)
    {
        $infos = array_merge(
            $infos,
            array(
                'list_css' => $this->getCSS(),
                'list_alerts' => $this->ferme->alerts->getAll(),
                'list_js' => $this->getJS(),
            )
        );
        return $infos;
    }

    /**
     * Ajoute les CSS du themes
     */
    private function getCSS()
    {
        $cssPath =  $this->getThemePath() . "/css/";
        $listCss = array();
        foreach ($this->getFiles($cssPath) as $file) {
            $listCss[] = $file;
        }
        return $listCss;
    }

    /**
     * Ajoute les JavaScript du themes
     */
    private function getJS()
    {
        $jsPath = $this->getThemePath() . "/js/";
        $listJs = array();
        foreach ($this->getFiles($jsPath) as $file) {
            $listJs[] = $file;
        }
        return $listJs;
    }

    /**
     * Liste des fichiers dans un repertoire
     * @todo Filtrer les résultat par extension (css ou js)
     *
     * @param $path
     * @return mixed
     */
    private function getFiles($path)
    {
        $fileArray = array();
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                $entryPath = $path . $entry;
                if ("." != $entry && ".." != $entry && is_file($entryPath)
                ) {
                    $fileArray[] = $entryPath;
                }
            }
            closedir($handle);
        }
        return $fileArray;
    }

    /**
     * Ajoute les informations concernant l'utilisateur connecté.
     * @param  array  $infos le tableau a completer.
     * @return array
     */
    private function addUserInfos(&$infos)
    {
        $infos = array_merge(
            $infos,
            array(
                'username' => $this->ferme->users->whoIsLogged(),
                'logged' => $this->ferme->users->isLogged(),
            )
        );
        return $infos;
    }

    /**
    * Génère un tableau d'information sur les objets a partir de la liste
    * de ces objets (Archive ou Wiki)
    * @param  array $listObjects liste d'objets dont il faut récupérer les
    * informations
    * @return array               Information sur les objets
    */
    protected function object2Infos($listObjects)
    {
        $listInfos = array();
        foreach ($listObjects as $name => $object) {
            $listInfos[$name] = $object->getInfos();
        }
        return $listInfos;
    }


    private function getThemePath()
    {
        return 'themes/' . $this->ferme->config['template'];
    }
}
