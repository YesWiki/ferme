<?php
namespace Ferme;

/**
 * Classe Controller
 *
 * gère les entrées ($this->post et $this->get)
 * @package Ferme
 * @author  Florestan Bredow <florestan.bredow@supagro.fr>
 * @version 0.0.1 (Git: $Id$)
 * @copyright 2015 Florestan Bredow
 */
class Controller
{
    private $config;
    private $ferme;
    private $get;
    private $post;

    public function __construct($get, $post)
    {
        $this->get = $get;
        $this->post = $post;
        $this->config = new Configuration('ferme.config.php');
        $this->ferme = new Ferme($this->config);
    }

    public function run()
    {
        // Si la vue n'est pas définie dans l'URL.
        $view = 'default';
        if (isset($this->get['view'])) {
            $view = $this->get['view'];
        }

        switch ($view) {
            case 'admin':
                try {
                    $this->ferme->loadWikis(true);
                } catch (\Exception $e) {
                    $this->ferme->addAlert($e->getMessage(), "error");
                }
                $this->ferme->loadArchives();
                break;

            default:
                try {
                    $this->ferme->loadWikis(false);
                } catch (\Exception $e) {
                    $this->ferme->addAlert($e->getMessage(), "error");
                }
                break;
        }

        if (isset($this->get['action'])) {
            $this->runAction($this->get['action']);
            $this->reload($view);
        }

        switch ($view) {
            case 'admin':
                $this->view = new View($this->ferme);
                if (!$this->ferme->isLogged()) {
                    $this->view->show('auth.html');
                    break;
                }
                $this->view->show('admin.html');
                break;
            case 'ajax':
                $this->view = new View($this->ferme);
                if (isset($this->get['query'])) {
                    switch ($this->get['query']) {
                        case 'search':
                            $string = '*';
                            if (isset($this->get['string'])) {
                                $string = $this->get['string'];
                                if ('' === $string) {
                                    $string = '*';
                                }
                            }

                            $this->view->ajax(
                                $this->get['query'],
                                'views/list_wikis.html',
                                array('string' => $string)
                            );
                            break;

                        default:
                            # code...
                            break;
                    }

                    break;
                }
            // Si query n'est pas définis on utiliser le traitement par
            // défaut.
            default:
                $this->view = new View($this->ferme);
                $this->view->show();
                break;
        }
    }

    private function runAction($action)
    {
        switch ($action) {
            case 'addWiki':
                $this->addWiki();
                break;
            case 'login':
                if (isset($this->post['username'])
                    and isset($this->post['password'])
                ) {
                    $this->ferme->login(
                        $this->post['username'],
                        $this->post['password']
                    );
                }
                break;

            case 'logout':
                $this->ferme->logout();
                break;

            case 'delete':
                if (isset($this->get['name'])) {
                    try {
                        $this->ferme->delete($this->get['name']);
                        $this->ferme->addAlert(
                            "Wiki " . $this->get['name']
                            . " : Supprimé avec succès"
                        );
                    } catch (\Exception $e) {
                        $this->ferme->addAlert($e->getMessage(), "error");
                    }
                }
                break;
            case 'updateConfiguration':
                if (isset($this->get['name'])) {
                    try {
                        $this->ferme->updateConfiguration($this->get['name']);
                        $this->ferme->addAlert(
                            "Wiki " . $this->get['name']
                            . " : configuration mise à jour avec succès"
                        );
                    } catch (\Exception $e) {
                        $this->ferme->addAlert($e->getMessage(), "error");
                    }
                }
                break;
            case 'archive':
                if (isset($this->get['name'])) {
                    try {
                        $this->ferme->archiveWiki($this->get['name']);
                        $this->ferme->addAlert(
                            "Wiki " . $this->get['name']
                            . " : Sauvegardé avec succès"
                        );
                    } catch (\Exception $e) {
                        $this->ferme->addAlert($e->getMessage(), "error");
                    }
                }
                break;

            case 'restore':
                if (isset($this->get['name'])) {
                    try {
                        $this->ferme->restore($this->get['name']);
                        $this->ferme->addAlert(
                            "Archive : " . $this->get['name']
                            . " : Restaurée avec succès"
                        );
                    } catch (\Exception $e) {
                        $this->ferme->addAlert($e->getMessage(), "error");
                    }
                }
                break;

            case 'deleteArchive':
                if (isset($this->get['name'])) {
                    try {
                        $this->ferme->deleteArchive($this->get['name']);
                        $this->ferme->addAlert(
                            "Archive : "
                            . $this->get['name']
                            . " : Supprimé avec succès"
                        );
                    } catch (\Exception $e) {
                        $this->ferme->addAlert($e->getMessage(), "error");
                    }
                }
                break;
            case 'exportMailing':
                $this->view->exportMailing("mailing.csv");
                break;
            case 'download':
                if (isset($this->get['archive'])) {
                    $download = new Download($this->get['archive'], $this->ferme);
                    $download->serve();
                }
                break;
            default:
                // Action inconnue ?
                break;
        }
    }

    private function addWiki()
    {
        if (!$this->isHashcashValid()) {
            $this->ferme->addAlert(
                'La plantation de wiki est une activité délicate qui'
                . ' ne doit pas être effectuée par un robot. (Pensez à'
                . ' activer JavaScript)'
            );
            $this->reload();
        }

        if (!isset($this->post['wikiName'])
            or !isset($this->post['mail'])
            or !isset($this->post['description'])
        ) {
            $this->ferme->addAlert("Formulaire incomplet.");
            $this->reload();
        }

        try {
            $wikiPath = $this->ferme->createWiki(
                $this->post['wikiName'],
                $this->post['mail'],
                $this->post['description']
            );
        } catch (\Exception $e) {
            $this->ferme->addAlert($e->getMessage());
            $this->reload();
        }

        $this->ferme->addAlert(
            '<a href="' . $this->config['base_url']
            . $wikiPath . '">Visiter le nouveau wiki</a>'
        );
    }

    private function isHashcashValid()
    {
        require_once 'app/secret/wp-hashcash.php';
        if (!isset($this->post["hashcash_value"])
            || hashcash_field_value() != $this->post["hashcash_value"]) {
            return false;
        }
        return true;
    }

    private function reload($view = 'default')
    {
        $url = $this->ferme->getURL();
        if ('admin' == $view) {
            $url = $this->ferme->getAdminURL();
        }
        header("Location: " . $url);
        exit();
    }
}
