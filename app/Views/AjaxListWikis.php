<?php
namespace Ferme\Views;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class AjaxListWikis extends TwigView
{
    private $filter = "*";

    /**
     * define filter for search
     * @param $string
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Get all informations needed by the view
     * @return array needed informations for the view
     */
    protected function compileInfos()
    {
        $infos = array();

        $infos['list_wikis'] = $this->object2Infos(
            $this->ferme->wikis->searchNoCaseType($this->filter)
        );

        return $infos;
    }
}
