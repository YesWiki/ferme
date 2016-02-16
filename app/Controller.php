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
            $this->download($get);
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
        if (isset($get['query'])) {
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
                        $get['query'],
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

    private function download($get)
    {
        if (isset($get['archive'])) {
            $download = new Download($get['archive'], $this->ferme);
            $download->serve();
        }
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
                $this->ferme->loadWikis(true);
                break;

            case 'login':
                $this->actionLogin($post);
                break;

            case 'logout':
                $this->ferme->logout();
                break;

            case 'delete':
                $this->actionDelete($get);
                $this->ferme->loadWikis(true);
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
                $this->ferme->loadWikis(true);
                break;

            case 'deleteArchive':
                $this->actionDeleteArchive($get);
                $this->ferme->loadArchives();
                break;
        }
    }

    private function actionDeleteArchive($get)
    {
        if (isset($get['name'])) {
            $this->ferme->deleteArchive($get['name']);
        }
    }

    private function actionRestore($get)
    {
        if (isset($get['name'])) {
            $this->ferme->restore($get['name']);
        }
    }

    private function actionArchive($get)
    {
        if (isset($get['name'])) {
            $this->ferme->archiveWiki($get['name']);
        }
    }

    private function actionUpdateConfiguration($get)
    {
        if (isset($get['name'])) {
            $this->ferme->updateConfiguration($get['name']);
        }
    }

    private function actionLogin($post)
    {
        if (isset($post['username']) and isset($post['password'])) {
            $this->ferme->login($post['username'], $post['password']);
        }
    }

    private function actionDelete($get)
    {
        if (isset($get['name'])) {
            $this->ferme->delete($get['name']);
        }
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
