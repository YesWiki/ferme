<?php
namespace Ferme;

/**
 * Classe wiki
 *
 * Gère l'afficahge de la Ferme
 * @package Ferme
 * @author  Florestan Bredow <florestan.bredow@supagro.fr>
 * @version 0.1.1 (Git: $Id$)
 * @copyright 2013 Florestan Bredow
 */

class View
{
    protected $model;
    protected $theme;
    protected $config;
    protected $twig_loader;
    protected $twig;

    /**
     * Constructeur
     *
     * @param $model
     */
    public function __construct($model)
    {
        $this->model = $model;
        $this->alerts = array();
        $this->config = $model->getConfig();
        $this->theme = $this->config->getParameter('template');
        $this->twig_loader = new \Twig_Loader_Filesystem(
            'themes/' . $this->theme
        );
        $this->twig = new \Twig_Environment($this->twig_loader);
    }

    /**
     * Affiche le wiki. ($template permet de forcer le theme)
     * @todo Ajouter choix du template
     *
     * @param $template
     */
    public function show($template = 'default.html')
    {
        $wiki_name = '';
        $description = '';
        $mail = '';

        if (isset($_POST['wikiName'])) {
            $wiki_name = $_POST['wikiName'];
        }

        if (isset($_POST['description'])) {
            $wiki_name = $_POST['description'];
        }

        if (isset($_POST['mail'])) {
            $wiki_name = $_POST['mail'];
        }

        echo $this->twig->render(
            $template,
            array(
                'list_css' => $this->getCSS(),
                'list_alerts' => $this->model->getListAlerts(),
                'list_js' => $this->getJS(),
                'list_themes' => $this->model->getThemesList(),
                'list_archives' => $this->getArchives(),
                'list_wikis' => $this->getWikis(),
                'wiki_name' => $wiki_name,
                'mail' => $mail,
                'description' => $description,
                'hashcash_url' => $this->HashCash(),
                'username' => $this->model->whoIsLogged(),
                'logged' => $this->model->isLogged(),
            )
        );
    }

    /**
     * Retourne un tableau avec la liste des wikis
     *
     * @param $template
     * @return null
     */
    private function getWikis()
    {
        $list_wikis = array();

        $this->model->resetIndex();
        do {
            $wiki = $this->model->getCur();
            $list_wikis[] = $wiki->getInfos();
        } while ($this->model->getNext());

        return $list_wikis;
    }

    /**
     * Retourne un tableau avec la liste des archives
     *
     * @param $template
     * @return null
     */
    private function getArchives()
    {
        $list_archives = array();

        // Si c'est la page principale les archives ne sont pas prises en
        // compte
        if ($this->model->nbArchives() != 0) {
            $this->model->resetIndexArchives();
            do {
                $archive = $this->model->getCurArchive();
                $list_archives[] = $archive->getInfos();
            } while ($this->model->getNextArchive());
        }

        return $list_archives;
    }

    /**
     * HASH-CASH : Charge le JavaScript qui génére la clé.
     */
    private function hashCash()
    {
        $hashcash_url =
        $this->config->getParameter('base_url')
        . 'app/wp-hashcash-js.php?siteurl='
        . $this->config->getParameter('base_url');

        return $hashcash_url;
    }

    /**
     * Envois un email de confirmation
     * @todo : ne valider l'envois que si le paramêtre mail est a 1 dans la
     * configuration
     *
     * @param $mail
     * @param $wikiName
     */
    public function sendConfirmationMail($mail, $wikiName)
    {

    }

    /**
     * Ajoute les CSS du themes
     */
    public function getCSS()
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
    public function getJS()
    {
        $js_path = "themes/" . $this->theme . "/js/";
        $list_js = array();
        foreach ($this->getFiles($js_path) as $file) {
            $list_js[] = $file;
        }
        return $list_js;
    }

    /**
     * Envois un fichier CSV contenant la liste des couples adresse mail / wiki
     *
     * @param string $filename nom du fichier exporté.
     */
    public function exportMailing($filename)
    {
        $csv = new CSV();

        if ($this->model->nbWikis() <= 0) {
            $csv->printFile($filename);
            exit;
        }

        $this->model->resetIndex();
        do {
            $wiki = $this->model->getCur();
            $infos = $wiki->getInfos();
            $csv->insert(
                array(
                    $infos['name'],
                    $infos['mail'],
                    str_replace('wakka.php?wiki=', '', $infos['url']),
                )
            );
        } while ($this->model->getNext());

        $csv->printFile($filename);
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
