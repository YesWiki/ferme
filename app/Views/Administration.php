<?php
namespace Ferme\Views;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class Administration extends TwigView
{
    /**
     * Get all informations needed by the view
     * @return array needed informations for the view
     */
    protected function compileInfos()
    {
        $infos = array();

        // Evalue l'espace DB et Fichier de chaque Wiki, utile uniquement pour
        // cette vue et tres lourd pour les ressources (TODO)
        $this->ferme->wikis->calSize();

        $infos['list_wikis'] =
            $this->object2Infos($this->ferme->wikis->search());

        $infos['list_archives'] =
            $this->object2Infos($this->ferme->archives->search());

        return $infos;
    }
}
