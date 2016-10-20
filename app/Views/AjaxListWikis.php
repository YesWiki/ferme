<?php
namespace Ferme\Views;

class AjaxListWikis extends TwigView
{
    private $filter = "*";

    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    protected function compileInfos()
    {
        $listInfos = array();

        $listInfos['list_wikis'] =
        $this->object2Infos($this->ferme->wikis->search($this->filter));
        return $listInfos;
    }
}
