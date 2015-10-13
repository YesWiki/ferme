<?php
namespace Ferme;

session_start();

$loader = require __DIR__ . '/vendor/autoload.php';

$config = new Configuration('ferme.config.php');
$ferme = new Ferme($config);
$view = new View($ferme);

$ferme->refresh();
$ferme->refreshArchives();

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'delete':
            if (isset($_GET['name'])) {
                try {
                    $ferme->delete($_GET['name']);
                    $view->addAlert(
                        "Wiki " . $_GET['name']
                        . " : Supprimé avec succès"
                    );
                } catch (Exception $e) {
                    $view->addAlert($e->getMessage(), "error");
                }
                header("Location: " . $ferme->getAdminURL());
                exit;
            }
            break;
        case 'save':
            if (isset($_GET['name'])) {
                try {
                    $ferme->save($_GET['name']);
                    $view->addAlert(
                        "Wiki " . $_GET['name']
                        . " : Sauvegardé avec succès"
                    );
                } catch (\Exception $e) {
                    $view->addAlert($e->getMessage(), "error");
                }
            }
            header("Location: " . $ferme->getAdminURL());
            exit;

            break;
        case 'restore':
            if (isset($_GET['name'])) {
                try {
                    $ferme->restore($_GET['name']);
                    $view->addAlert(
                        "Archive : " . $_GET['name']
                        . " : Restaurée avec succès"
                    );
                } catch (\Exception $e) {
                    $view->addAlert($e->getMessage(), "error");
                }
                header("Location: " . $ferme->getAdminURL());
                exit;
            }
            break;
        case 'deleteArchive':
            if (isset($_GET['name'])) {
                try {
                    $ferme->deleteArchive($_GET['name']);
                    $view->addAlert(
                        "Archive : " . $_GET['name']
                        . " : Supprimé avec succès"
                    );
                } catch (\Exception $e) {
                    $view->addAlert($e->getMessage(), "error");
                }
                header("Location: " . $ferme->getAdminURL());
                exit;
            }
            break;
        case 'exportMailing':
            $view->exportMailing("mailing.csv");
            header("Location: " . $ferme->getAdminURL());
            break;
        default:
            # rien, vraiment...
            break;
    }
}

$view->show("admin");