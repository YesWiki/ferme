<?php
namespace Ferme;

/**
 * Classe Controller
 *
 * gère les entrées ($post et $get)
 * @package Ferme
 * @author  Florestan Bredow <florestan.bredow@supagro.fr>
 * @version 0.0.1 (Git: $Id$)
 * @copyright 2015 Florestan Bredow
 */
class Controller
{
    private $config;
    private $ferme;

    public function __construct()
    {
        $this->config = new Configuration('ferme.config.php');
        $this->ferme = new Ferme($this->config);
    }

    public function run($get, $post)
    {
        $this->ferme->loadWikis(true);
        $this->ferme->loadArchives();

        if (isset($get['download'])) {
            $this->download($get['download']);
            return;
        }

        if (isset($get['action'])) {
            $this->action($get, $post);
        }

        $view = 'default';
        if (isset($get['view'])) {
            $view = $get['view'];
        }

        if ($view === 'ajax') {
            $this->ajax($get);
            return;
        }

        $this->showHtml($view);
    }

    private function ajax($get)
    {
        $view = new View($this->ferme);
        if (isset($get['query']) and ($get['query'] === 'search')) {
            switch ($get['query']) {
                case 'search':
                    $string = '*';
                    if (isset($get['string'])) {
                        $string = $get['string'];
                        if ('' === $string) {
                            $string = '*';
                        }
                    }
                    $view->ajax(
                        'views/list_wikis.html',
                        array('string' => $string)
                    );
                    break;
                default:
                    # code...
                    break;
            }
        }
    }

    private function download($download)
    {
        $download = new Download($download, $this->ferme);
        $download->serve();
    }

    private function showHtml($view)
    {
        $instView = new View($this->ferme);
        switch ($view) {
            case 'admin':
                if (!$this->ferme->isLogged()) {
                    $instView->show('auth.html');
                    break;
                }
                $instView->show('admin.html');
                break;
            case 'exportMailing':
                $view = new View($this->ferme);
                $view->exportMailing("mailing.csv");
                break;
            default:
                $instView->show();
                break;
        }
    }

    private function action($get, $post)
    {
        switch ($get['action']) {
            case 'addWiki':
                $this->actionAddWiki($post);
                $this->ferme->loadWikis();
                break;

            case 'login':
                $this->actionLogin($post);
                break;

            case 'logout':
                $this->ferme->logout();
                break;

            case 'delete':
                $this->actionDelete($get);
                break;

            case 'updateConfiguration':
                $this->actionUpdateConfiguration($get);
                break;

            case 'archive':
                $this->actionArchive($get);
                $this->ferme->loadArchives();
                break;

            case 'restore':
                $this->actionRestore($get);
                $this->ferme->loadWikis();
                break;

            case 'deleteArchive':
                $this->actionDeleteArchive($get);
                break;
        }
    }

    private function actionDeleteArchive($get)
    {
        if (!isset($get['name'])) {
            $this->addAlert(
                "Paramètres manquant pour la suppression de l'archive."
            );
        }
        $this->ferme->deleteArchive($get['name']);
        $this->ferme->addAlert(
            "L'archive " . $get['name'] . " a été supprimée avec succès"
        );
    }

    private function actionRestore($get)
    {
        if (!isset($get['name'])) {
            $this->addAlert(
                "Paramètres manquant pour la restauration de l'archive."
            );
        }
        $this->ferme->restore($get['name']);
        $this->ferme->addAlert(
            "L'archive " . $get['name'] . " a été restaurée avec succès."
        );
    }

    private function actionArchive($get)
    {
        if (!isset($get['name'])) {
            $this->addAlert(
                "Paramètres manquant pour créer l'archive."
            );
        }
        $this->ferme->archiveWiki($get['name']);
        $this->ferme->addAlert(
            "Le wiki " . $get['name'] . " a été archivé avec succès."
        );
    }

    private function actionUpdateConfiguration($get)
    {
        if (!isset($get['name'])) {
            $this->addAlert(
                "Paramètres manquant pour mettre à jour la configuration."
            );
        }

        $this->ferme->updateConfiguration($get['name']);
        $this->ferme->addAlert(
            "La configuration de " . $get['name'] . " a été mise à "
            . "jour avec succès."
        );
    }

    private function actionLogin($post)
    {
        if (!(isset($post['username']) and isset($post['password']))) {
            $this->ferme->login($post['username'], $post['password']);
        }
    }

    private function actionDelete($get)
    {
        if (!isset($get['name'])) {
            $this->addAlert(
                "Paramètres manquant pour la suppression du wiki."
            );
        }
        $this->ferme->delete($get['name']);
        $this->ferme->addAlert(
            "Le wiki " . $get['name'] . " a été supprimée avec succès"
        );

    }

    private function actionAddWiki($post)
    {
        if (!$this->isHashcashValid($post)) {
            $this->ferme->addAlert(
                'La plantation de wiki est une activité délicate qui'
                . ' ne doit pas être effectuée par un robot. (Pensez à'
                . ' activer JavaScript)'
            );
            return;
        }

        if (!isset($post['wikiName'])
            or !isset($post['mail'])
            or !isset($post['description'])
        ) {
            $this->ferme->addAlert("Formulaire incomplet.");
            return;
        }

        try {
            $wikiPath = $this->ferme->createWiki(
                $post['wikiName'],
                $post['mail'],
                $post['description']
            );
        } catch (\Exception $e) {
            $this->ferme->addAlert($e->getMessage());
            return;
        }

        //$this->ferme->loadWikis();

        $this->ferme->addAlert(
            '<a href="' . $this->config['base_url']
            . $wikiPath . '">Visiter le nouveau wiki</a>'
        );
    }

    private function isHashcashValid($post)
    {
        require_once 'app/secret/wp-hashcash.php';
        if (!isset($post["hashcash_value"])
            || hashcash_field_value() != $post["hashcash_value"]) {
            return false;
        }
        return true;
    }
}
