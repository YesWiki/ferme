<?php
namespace Ferme\Views;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class Authentification extends TwigView
{
    /**
     * Get all informations needed by the view
     * @return array needed informations for the view
     */
    protected function compileInfos()
    {
        // Do nothing, all necessary data are already added by class TwigView
        return array();
    }
}
