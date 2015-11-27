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
    protected $theme;
    protected $config;
    protected $twig_loader;
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
        $this->config = $ferme->getConfig();
        $this->theme = $this->config['template'];
        $this->twig_loader = new \Twig_Loader_Filesystem(
            'themes/' . $this->theme
        );
        $this->twig = new \Twig_Environment($this->twig_loader);
    }

    /**
     * Affiche un template twig
     * @todo Ajouter choix du template
     *
     * @param $template
     */
    public function show($template = 'default.html')
    {
        $list_infos = array();

        if ('admin.html' === $template) {
            $list_infos['list_archives'] =
            $this->object2Infos($this->ferme->searchArchives());
        }

        if ('default.html' === $template) {
            $this->addPostInfos($list_infos);
            $list_infos['hashcash_url'] = $this->HashCash();
        }

        $this->addThemesInfos($list_infos);
        $this->addUserInfos($list_infos);

        $list_infos['list_wikis'] =
        $this->object2Infos($this->ferme->searchWikis());

        echo $this->twig->render($template, $list_infos);
    }

    /**
     * Affiche le résultat d'une requete ajax
     * @todo  Premier jet a améliorer et completer.
     *
     * @param  string $query    [description]
     * @param  string $template [description]
     * @param  array $args     [description]
     */
    public function ajax($query, $template, $args = null)
    {
        $string = isset($args['string']) ? $args['string'] : '*';

        $list_infos = array();

        $list_infos['list_wikis'] =
        $this->object2Infos($this->ferme->searchWikis($string));

        echo $this->twig->render($template, $list_infos);
    }

    /**
     * Ajout les informations contenu dans la variable $_POST (permet de
     * conserver le contenu des formulaires non validé)
     * @param array $infos le tableau d'information a completer.
     */
    private function addPostInfos(&$infos)
    {
        $infos['wiki_name'] =
        isset($_POST['wikiName']) ? $_POST['wikiName'] : '';

        $infos['description'] =
        isset($_POST['description']) ? $_POST['description'] : '';

        $infos['mail'] =
        isset($_POST['mail']) ? $_POST['mail'] : '';

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
                'list_alerts' => $this->ferme->getListAlerts(),
                'list_js' => $this->getJS(),
                'list_themes' => $this->ferme->getThemesList(),
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
                'username' => $this->ferme->whoIsLogged(),
                'logged' => $this->ferme->isLogged(),
            )
        );
        return $infos;
    }

    /**
     * Génère un tableau d'information sur les objets a partir de la liste
     * de ces objets (Archive ou Wiki)
     * @param  array $list_objects liste d'objets dont il faut récupérer les
     * informations
     * @return array               Information sur les objets
     */
    private function object2Infos($list_objects)
    {
        $list_infos = array();
        foreach ($list_objects as $name => $object) {
            $list_infos[$name] = $object->getInfos();
        }
        return $list_infos;
    }

    /**
     * Génère l'URL vers le javascript qui calcule le hash
     * @return string URL vers le javascript
     */
    private function hashCash()
    {
        $hashcash_url =
        $this->config['base_url']
        . 'app/wp-hashcash-js.php?siteurl='
        . $this->config['base_url'];

        return $hashcash_url;
    }

    /**
     * Envois un fichier CSV contenant la liste des couples adresse mail / wiki
     *
     * @param string $filename nom du fichier exporté.
     */
    public function exportMailing($filename)
    {
        $csv = new CSV();

        if ($this->ferme->countWikis() <= 0) {
            $csv->printFile($filename);
            exit;
        }

        $this->ferme->resetIndexWikis();
        do {
            $wiki = $this->ferme->getCurrentWiki();
            $infos = $wiki->getInfos();
            $csv->insert(
                array(
                    $infos['name'],
                    $infos['mail'],
                    str_replace('wakka.php?wiki=', '', $infos['url']),
                )
            );
        } while ($this->ferme->getNextWiki());

        $csv->printFile($filename);
    }

    /**
     * Ajoute les CSS du themes
     */
    private function getCSS()
    {
        $css_path = "themes/" . $this->theme . "/css/";
        $list_css = array();
        foreach ($this->getFiles($css_path) as $file) {
            $list_css[] = $file;
        }
        return $list_css;
    }

    /**
     * Ajoute les JavaScript du themes
     */
    private function getJS()
    {
        $js_path = "themes/" . $this->theme . "/js/";
        $list_js = array();
        foreach ($this->getFiles($js_path) as $file) {
            $list_js[] = $file;
        }
        return $list_js;
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
        $file_array = array();
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                $entry_path = $path . $entry;
                if ("." != $entry && ".." != $entry && is_file($entry_path)
                ) {
                    $file_array[] = $entry_path;
                }
            }
            closedir($handle);
        }
        return $file_array;
    }
}
