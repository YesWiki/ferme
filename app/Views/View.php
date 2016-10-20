<?php
namespace Ferme\Views;

/**
 * Classe wiki
 *
 * Gère l'afficahge de la Ferme
 * @package Ferme
 * @author  Florestan Bredow <florestan.bredow@supagro.fr>
 * @version 0.2.1 (Git: $Id$)
 * @copyright 2013 Florestan Bredow
 */

abstract class View
{
    protected $ferme;
    protected $alerts;

    /**
     * Constructeur
     *
     * @param $ferme
     */
    public function __construct($ferme)
    {
        $this->ferme = $ferme;
        $this->alerts = array();
    }

    abstract public function show();

}
