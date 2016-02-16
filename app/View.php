<?php
namespace Ferme;

/**
 * Classe wiki
 *
 * Gère l'afficahge de la Ferme
 * @package Ferme
 * @author  Florestan Bredow <florestan.bredow@supagro.fr>
 * @version 0.2.1 (Git: $Id$)
 * @copyright 2013 Florestan Bredow
 */

class View
{
    protected $ferme;
    protected $twigLoader;
    protected $twig;

    /**
     * Constructeur
     *
     * @param $ferme
     */
    public function __construct($ferme)
    {
        $this->ferme = $ferme;
        $this->alerts = array();
        $this->twigLoader = new \Twig_Loader_Filesystem(
            $this->getThemePath()
        );
        $this->twig = new \Twig_Environment($this->twigLoader);
    }

    /**
     * Affiche un template twig
     * @todo Ajouter choix du template
     *
     * @param $template
     */
    public function show($template = 'default.html')
    {
        $listInfos = array();

        if ('admin.html' === $template) {
            $listInfos['list_archives'] =
            $this->object2Infos($this->ferme->archives->search());
        }

        if ('default.html' === $template) {
            $this->addPostInfos($listInfos);
            $listInfos['hashcash_url'] = $this->HashCash();
        }

        $this->addThemesInfos($listInfos);
        $this->addUserInfos($listInfos);

        $listInfos['list_wikis'] =
        $this->object2Infos($this->ferme->wikis->search());

        echo $this->twig->render($template, $listInfos);
    }

     /**
      * Affiche le résultat d'une requete ajax
      * @todo  Premier jet a améliorer et completer.
      *
      * @param  string $template [description]
      * @param  array $args     [description]
      */
    public function ajax($template, $args = null)
    {
        $string = isset($args['string']) ? $args['string'] : '*';

        $listInfos = array();

        $listInfos['list_wikis'] =
        $this->object2Infos($this->ferme->wikis->search($string));

        echo $this->twig->render($template, $listInfos);
    }

    /**
     * Ajout les informations contenu dans la variable $_POST (permet de
     * conserver le contenu des formulaires non validé)
     * @param array $infos le tableau d'information a completer.
     */
    private function addPostInfos(&$infos)
    {
        $infos['wiki_name'] = '';
        if (filter_has_var(INPUT_POST, 'wiki_name')) {
            $infos['wiki_name'] = filter_input(
                INPUT_POST,
                'wiki_name',
                FILTER_SANITIZE_STRING
            );
        }

        $infos['description'] = '';
        if (filter_has_var(INPUT_POST, 'description')) {
            $infos['description'] = filter_input(
                INPUT_POST,
                'description',
                FILTER_SANITIZE_STRING
            );
        }

        $infos['mail'] = '';
        if (filter_has_var(INPUT_POST, 'mail')) {
            $infos['mail'] = filter_input(
                INPUT_POST,
                'mail',
                FILTER_SANITIZE_STRING
            );
        }

        return ($infos);
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
                'list_themes' => $this->getThemesList(),
            )
        );
        return $infos;
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
    private function object2Infos($listObjects)
    {
        $listInfos = array();
        foreach ($listObjects as $name => $object) {
            $listInfos[$name] = $object->getInfos();
        }
        return $listInfos;
    }

    /**
     * Génère l'URL vers le javascript qui calcule le hash
     * @return string URL vers le javascript
     */
    private function hashCash()
    {
        $hashcashUrl =
        'app/wp-hashcash-js.php?siteurl='
        . $this->ferme->config['base_url'];

        return $hashcashUrl;
    }

    /**
     * Envois un fichier CSV contenant la liste des couples adresse mail / wiki
     *
     * @param string $filename nom du fichier exporté.
     */
    public function exportMailing($filename)
    {
        $csv = new CSV();

        if ($this->ferme->wikis->count() <= 0) {
            $csv->printFile($filename);
            return;
        }

        reset($this->ferme->wikis);
        foreach ($this->ferme->wikis as $wiki) {
            $infos = $wiki->getInfos();
            $csv->insert(
                array(
                    $infos['name'],
                    $infos['mail'],
                    str_replace('wakka.php?wiki=', '', $infos['url']),
                )
            );
        }

        $csv->printFile($filename);
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
     * Retourne la liste des thèmes.
     * @return array tableau de tableau avec deux clés : name et thumb
     */
    private function getThemesList()
    {
        $themesList = array();

        include "packages/"
            . $this->ferme->config['source']
            . "/install.config.php";

        foreach ($config['themes'] as $key => $value) {
            $themesList[] = array(
                'name' => $key,
                'thumb' => $value['thumb'],
            );
        }
        return $themesList;
    }

    private function getThemePath()
    {
        return 'themes/' . $this->ferme->config['template'];
    }
}
