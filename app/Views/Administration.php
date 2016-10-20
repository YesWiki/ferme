<?php
namespace Ferme\Views;

class Administration extends TwigView
{
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
