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

        // Ajoute la date de dernière modification et l'espace occupés par le
        // repertoire 'files' aux informations sur le wiki.
        $listWiki = $this->ferme->wikis->search();
        foreach ($listWiki as $wiki) {
            $wiki->infos['LasPageModificationDateTime'] = $wiki->getLasPageModificationDateTime();
            $wiki->infos['FilesDiskUsage'] = $wiki->getFilesDiskUsage();
        }
        $infos['list_wikis'] = $this->object2Infos($listWiki);

        $infos['list_archives'] =
            $this->object2Infos($this->ferme->archives->search());

        return $infos;
    }
}
