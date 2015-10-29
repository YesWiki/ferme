<?php
namespace Ferme;

class Alerts
{
    /**
     * Ajoute une alerte a afficher.
     *
     * @param $text
     * @param $type
     */
    public function add($text, $type)
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
     * Renvois la list
     *
     * @param $template
     */
    public function getAll()
    {
        $list_alerts = array();

        //Affichage des alertes
        if (isset($_SESSION['alerts'])) {
            $i = 0;
            foreach ($_SESSION['alerts'] as $key => $alert) {
                $list_alerts[] = array(
                    'id' => "alert" . $key,
                    'text' => $alert['text'],
                    'type' => $alert['type'],
                );
            }
        }
        return $list_alerts;
    }

    public function removeAll()
    {
        unset($_SESSION['alerts']);
    }
}
