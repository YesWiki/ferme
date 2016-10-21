<?php
namespace Ferme\Views;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
abstract class TwigView extends View
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Get all informations needed by the view
     * @return array needed informations for the view
     */
    abstract protected function compileInfos();

    /**
     * Constructor
     * @param \Ferme\Ferme $ferme reference to model.
     */
    public function __construct($ferme)
    {
        parent::__construct($ferme);
        $twigLoader = new \Twig_Loader_Filesystem(
            'themes/' . $this->ferme->config['template']
        );
        $this->twig = new \Twig_Environment($twigLoader);
    }

    /**
     * Show the view
     * @return void
     */
    public function show()
    {
        $listInfos = $this->compileInfos();
        $listInfos = $this->addThemesInfos($listInfos);
        $listInfos = $this->addUserInfos($listInfos);
        echo $this->twig->render($this->getTemplateFilename(), $listInfos);
    }

    /**
     * @return string
     */
    private function getTemplateFilename()
    {
        $explodedClassName = explode('\\', get_class($this));
        $className = end($explodedClassName);
        return "$className.twig";
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
     * list CSS files present in "css" theme's folder
     * @return array
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
     * list JS files present in "js" theme's folder
     * @return array
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
     * List files in directory
     * @todo Filter results by extension (css ou js)
     * @todo use Iterators
     * @param $path Path to folder to scan
     * @return array
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
     * Add connected user's informations
     * @param  array  $infos Array to complete
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
    * transform a list of Archives / Wikis in array of informations
    * @param  array $listObjects object list
    * @return array objects informations
    */
    protected function object2Infos($listObjects)
    {
        $listInfos = array();
        foreach ($listObjects as $name => $object) {
            $listInfos[$name] = $object->getInfos();
        }
        return $listInfos;
    }
}
