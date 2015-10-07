<?php

session_start();

include_once('../php/ferme.class.php');
include_once('../php/view.class.php');

$ferme = new Ferme\Ferme("../ferme.config.php");
$view  = new Ferme\View($ferme);

//Pour éviter les problèmes de chemin : 
$ferme->config['ferme_path'] = "../wikis/";
$ferme->config['admin_path'] = "archives/";

$ferme->refresh();
$ferme->refreshArchives();

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'delete':
            if (isset($_GET['name'])) {
                try {
                    $ferme->delete($_GET['name']);
                    $view->addAlert(
                        "Wiki ".$_GET['name']
                        ." : Supprimé avec succès"
                    );
                } catch (Exception $e) {
                    $view->addAlert($e->getMessage(), "error");
                }
                header("Location: ".$ferme->getAdminURL());
                exit;
            }
            break;
        case 'save':
            if (isset($_GET['name'])) {
                try {
                    $ferme->save($_GET['name']);
                    $view->addAlert(
                        "Wiki ".$_GET['name']
                        ." : Sauvegardé avec succès"
                    );
                } catch (Exception $e) {
                    $view->addAlert($e->getMessage(), "error");
                }
            }
            header("Location: ".$ferme->getAdminURL());
            exit;

            break;
        case 'restore':
            if (isset($_GET['name'])) {
                try {
                    $ferme->restore($_GET['name']);
                    $view->addAlert(
                        "Archive : ".$_GET['name']
                        ." : Restaurée avec succès"
                    );
                } catch (Exception $e) {
                    $view->addAlert($e->getMessage(), "error");
                }
                header("Location: ".$ferme->getAdminURL());
                exit;
            }
            break;
        case 'deleteArchive':
            if (isset($_GET['name'])) {
                try {
                    $ferme->deleteArchive($_GET['name']);
                    $view->addAlert(
                        "Archive : ".$_GET['name']
                        ." : Supprimé avec succès"
                    );
                } catch (Exception $e) {
                    $view->addAlert($e->getMessage(), "error");
                }
                header("Location: ".$ferme->getAdminURL());
                exit;
            }
            break;
        default:
            # rien, vraiment...
            break;
    }
}

$view->show("admin");
