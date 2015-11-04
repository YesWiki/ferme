<?php
namespace Ferme;

/**
 * Classe Controller
 *
 * gère les entrées ($_POST et $_GET)
 * @package Ferme
 * @author  Florestan Bredow <florestan.bredow@supagro.fr>
 * @version 0.0.1 (Git: $Id$)
 * @copyright 2015 Florestan Bredow
 */
class Controller
{
    private $config;
    private $ferme;
    private $view;

    public function __construct()
    {
        $this->config = new Configuration('ferme.config.php');
        $this->ferme = new Ferme($this->config);
        $this->view = new View($this->ferme);
    }

    public function run()
    {
        // Si la vue n'est pas définie dans l'URL.
        $view = 'default';
        if (isset($_GET['view'])) {
            $view = $_GET['view'];
        }

        switch ($view) {
            case 'admin':
                $this->ferme->loadWikis(true);
                $this->ferme->loadArchives();
                break;

            default:
                $this->ferme->loadWikis(false);
                break;
        }

        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'addWiki':
                    $this->addWiki();
                    break;
                case 'login':
                    if (isset($_POST['username'])
                        and isset($_POST['password'])
                    ) {
                        $this->ferme->login(
                            $_POST['username'],
                            $_POST['password']
                        );
                    }
                    break;

                case 'logout':
                    $this->ferme->logout();
                    break;

                case 'delete':
                    if (isset($_GET['name'])) {
                        try {
                            $this->ferme->delete($_GET['name']);
                            $this->ferme->addAlert(
                                "Wiki " . $_GET['name']
                                . " : Supprimé avec succès"
                            );
                        } catch (\Exception $e) {
                            $this->ferme->addAlert($e->getMessage(), "error");
                        }
                    }
                    break;

                case 'archive':
                    if (isset($_GET['name'])) {
                        try {
                            $this->ferme->archiveWiki($_GET['name']);
                            $this->ferme->addAlert(
                                "Wiki " . $_GET['name']
                                . " : Sauvegardé avec succès"
                            );
                        } catch (\Exception $e) {
                            $this->ferme->addAlert($e->getMessage(), "error");
                        }
                    }
                    break;

                case 'restore':
                    if (isset($_GET['name'])) {
                        try {
                            $this->ferme->restore($_GET['name']);
                            $this->ferme->addAlert(
                                "Archive : " . $_GET['name']
                                . " : Restaurée avec succès"
                            );
                        } catch (\Exception $e) {
                            $this->ferme->addAlert($e->getMessage(), "error");
                        }
                    }
                    break;

                case 'deleteArchive':
                    if (isset($_GET['name'])) {
                        try {
                            $this->ferme->deleteArchive($_GET['name']);
                            $this->ferme->addAlert(
                                "Archive : "
                                . $_GET['name']
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
                default:
                    // Action inconnue ?
                    break;
            }
            $this->reload($view);
        }

        switch ($view) {
            case 'admin':
                if (!$this->ferme->isLogged()) {
                    $this->view->show('auth.html');
                    break;
                }
                $this->view->show('admin.html');
                break;
            case 'ajax':
                if (isset($_GET['query'])) {
                    switch ($_GET['query']) {
                        case 'search':
                            $string = '*';
                            if (isset($_GET['string'])) {
                                $string = $_GET['string'];
                                if ('' === $string) {
                                    $string = '*';
                                }
                            }

                            $this->view->ajax(
                                $_GET['query'],
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
                $this->view->show();
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

        if (!isset($_POST['wikiName'])
            or !isset($_POST['mail'])
            or !isset($_POST['description'])
        ) {
            $this->ferme->addAlert("Formulaire incomplet.");
            $this->reload();
        }

        try {
            $wiki_path = $this->ferme->createWiki(
                $_POST['wikiName'],
                $_POST['mail'],
                $_POST['description']
            );
        } catch (\Exception $e) {
            $this->ferme->addAlert($e->getMessage());
            $this->reload();
        }

        $this->ferme->addAlert(
            '<a href="' . $this->config->getParameter('base_url')
            . $wiki_path . '">Visiter le nouveau wiki</a>'
        );
    }

    private function isHashcashValid()
    {
        require_once 'app/secret/wp-hashcash.php';
        if (!isset($_POST["hashcash_value"])
            || hashcash_field_value() != $_POST["hashcash_value"]) {
            return false;
        }
        return true;
    }

    private function reload($view = 'default')
    {
        if ('admin' == $view) {
            header("Location: " . $this->ferme->getAdminURL());
        } else {
            header("Location: " . $this->ferme->getURL());
        }
        exit();
    }
}
