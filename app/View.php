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
    protected $ferme;
    protected $alerts;
    protected $theme;
    protected $config;

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

        $this->theme = $this->config->getParameter('template');
    }

    /**
     * Affiche le wiki. ($template permet de forcer le theme)
     * @todo Ajouter choix du template
     *
     * @param $template
     */
    public function show($template = "")
    {
        if ("" == $template) {
            $template = $this->theme;
        } else {
            $this->theme = $template;
        }

        $squelette_path = "themes/" . $template . "/squelette/" . $template . ".phtml";

        if (!is_file($squelette_path)) {
            die("Template introuvable. (" . $squelette_path . ").");
        }
        include $squelette_path;
    }

    /**
     * Affiche la liste des Themes selon le template fournis
     *
     * @param $template
     */
    private function printThemes($template = "theme.phtml")
    {
        $themesList = $this->ferme->getThemesList();

        $i = 0;
        foreach ($themesList as $theme) {
            include "themes/" . $this->theme . "/squelette/" . $template;
        }
        unset($themesList);
    }

    /**
     * Affiche la liste des Wikis selon le template fournis
     *
     * @param $template
     * @return null
     */
    private function printWikis($template = "wiki.phtml")
    {
        if ($this->ferme->nbWikis() <= 0) {
            return;
        }

        $this->ferme->resetIndex();
        do {
            $wiki = $this->ferme->getCur();
            $infos = $wiki->getInfos();
            include "themes/" . $this->theme . "/squelette/" . $template;

        } while ($this->ferme->getNext());
    }

    /**
     * Affiche la liste des Archives selon le template fournis
     *
     * @param $template
     * @return null
     */
    private function printArchives($template = "archive.phtml")
    {
        if ($this->ferme->nbArchives() <= 0) {
            return;
        }

        $this->ferme->resetIndexArchives();
        do {
            $archive = $this->ferme->getCurArchive();
            $infos = $archive->getInfos();
            include "themes/" . $this->theme . "/squelette/" . $template;

        } while ($this->ferme->getNextArchive());
    }

    /**
     * HASH-CASH : Charge le JavaScript qui génére la clé.
     */
    private function hashCash()
    {
        //TODO : Rendre ce code "portable"
        echo '<!--Protection HashCash -->
        <script type="text/javascript"
                src="' . $this->config->getParameter('base_url')
        . 'app/wp-hashcash-js.php?siteurl='
        . $this->config->getParameter('base_url')
        . '">
        </script>';
    }

    /**
     * Affiche la liste des alertes selon le template fournis.
     *
     * @param $template
     */
    public function printAlerts($template = "alert.phtml")
    {
        //Affichage des alertes
        if (isset($_SESSION['alerts'])) {
            $i = 0;
            foreach ($_SESSION['alerts'] as $key => $alert) {
                $id = "alert" . $key;
                include "themes/" . $this->theme . "/squelette/" . $template;
            }
        }
        unset($_SESSION['alerts']); //pour éviter qu'elle ne s'accumulent.
    }

    /**
     * Ajoute une alerte a afficher.
     *
     * @param $text
     * @param $type
     */
    public function addAlert($text, $type = "default")
    {
        if (!isset($_SESSION['alerts'])) {
            $_SESSION['alerts'] = array();
        }

        $_SESSION['alerts'][] = array(
            'text' => $text,
            'type' => $type,
        );
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
    public function printCSS()
    {
        $css_path = "themes/" . $this->theme . "/css/";
        foreach ($this->getFiles($css_path) as $file) {
            print("<link href=\"" . $file . "\" rel=\"stylesheet\">\n");
        }
    }

    /**
     * Ajoute les JavaScript du themes
     */
    public function printJS()
    {
        $js_path = "themes/" . $this->theme . "/js/";
        foreach ($this->getFiles($js_path) as $file) {
            print("<script src=\"" . $file . "\"></script>\n");
        }
    }

    /**
     * Envois un fichier CSV contenant la liste des couples adresse mail / wiki
     *
     * @param string $filename nom du fichier exporté.
     */
    public function exportMailing($filename)
    {
        $csv = new CSV();

        if ($this->ferme->nbWikis() <= 0) {
            $csv->printFile($filename);
            exit;
        }

        $this->ferme->resetIndex();
        do {
            $wiki = $this->ferme->getCur();
            $infos = $wiki->getInfos();
            $csv->insert(
                array(
                    $infos['name'],
                    $infos['mail'],
                    str_replace('wakka.php?wiki=', '', $infos['url']),
                )
            );
        } while ($this->ferme->getNext());

        $csv->printFile($filename);
    }

    /**
     * Liste des fichiers dans un repertoire
     * @todo Filtrer les résultat par extension
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
