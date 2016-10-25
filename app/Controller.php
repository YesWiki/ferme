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
    private $ferme;

    public function __construct($ferme)
    {
        $this->ferme = $ferme;
    }

    public function run($get, $post)
    {
        $this->ferme->wikis->load();
        $this->ferme->archives->load();

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
        $view = new Views\AjaxListWikis($this->ferme);
        if (isset($get['query']) and ($get['query'] === 'search')) {
            switch ($get['query']) {
                case 'search':
                    $string = '*';
                    if (isset($get['string'])) {
                        $string = $get['string'];
                        if ('' === $string) {
                            $view->setFilter('*');
                        }
                    }
                    $view->setFilter($string);
                    $view->show();
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
        switch ($view) {
            case 'admin':
                if (!$this->ferme->users->isLogged()) {
                    $view = new Views\Authentification($this->ferme);
                    $view->show();
                    break;
                }
                $view = new Views\Administration($this->ferme);
                $view->show();
                break;
            case 'exportMailing':
                $view = new Views\CsvMailing($this->ferme);
                $view->show();
                break;
            default:
                $view = new Views\Home($this->ferme);
                $view->show();
                break;
        }
    }

    private function action($get, $post)
    {
        $className = "Ferme\\Actions\\" . ucfirst($get['action']);
        if (!class_exists($className)) {
            return;
        }

        $action = new $className($this->ferme, $get, $post);
        $action->execute();
    }
}
