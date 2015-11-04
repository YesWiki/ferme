<?php
namespace Ferme;

/**
 * Gère les alertes
 *
 * Utilise $_SESSION pour stocker les alertes et les retrouver. La session doit
 * être initialisée.
 *
 * @package  Ferme
 * @author   Florestan Bredow <florestan.bredow@supagro.fr>
 * @license
 */
class Alerts
{
    /**
     * Ajoute une alerte
     * @param string $text descriptif de l'alerte
     * @param string $type type d'alerte notice, warning ou error.
     */
    public function add($text, $type = 'error')
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
     * Retourne la liste des erreurs.
     *
     * La liste des alertes est retournée sous forme de tableau. Chaque alerte
     * est dans sous tableau avec les clés :
     *  - id : indentifiant unique de l'alerte (pour cette liste)
     *  - text : description de l'alerte
     *  - type : type d'alerte notice, warning ou error.
     * @return array Liste des tableaux
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

    /**
     * Vide la liste d'alerte
     */
    public function removeAll()
    {
        unset($_SESSION['alerts']);
    }
}
