<?php
namespace Ferme\Views;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class Home extends TwigView
{
    /**
     * Get all informations needed by the view
     * @return array needed informations for the view
     */
    protected function compileInfos()
    {
        $infos = array();

        $infos['wiki_name'] = '';
        if (filter_has_var(INPUT_POST, 'wiki_name')) {
            $infos['wiki_name'] = filter_input(
                INPUT_POST,
                'wiki_name',
                FILTER_SANITIZE_STRING
            );
        }

        $infos['description'] = '';
        if (filter_has_var(INPUT_POST, 'description')) {
            $infos['description'] = filter_input(
                INPUT_POST,
                'description',
                FILTER_SANITIZE_STRING
            );
        }

        $infos['mail'] = '';
        if (filter_has_var(INPUT_POST, 'mail')) {
            $infos['mail'] = filter_input(
                INPUT_POST,
                'mail',
                FILTER_SANITIZE_STRING
            );
        }

        $infos['list_wikis'] = $this->object2Infos(
            $this->ferme->wikis->search()
        );

        $infos['list_themes'] = $this->getThemesList();

        $infos['hashcash_url'] = $this->HashCash();

        return ($infos);
    }

    /**
     * Make hashcash URL
     * @return string URL hashcash javascript
     */
    private function hashCash()
    {
        $hashcashUrl =
        'app/wp-hashcash-js.php?siteurl='
        . $this->ferme->config['base_url'];

        return $hashcashUrl;
    }

    /**
     * return list of theme available for new wiki
     * @return array of array with 2 keys : name et thumb
     */
    private function getThemesList()
    {
        $themesList = array();

        include "packages/"
            . $this->ferme->config['source']
            . "/install.config.php";

        foreach ($config['themes'] as $themeName => $themeInfos) {
            $themesList[] = array(
                'name' => $themeName,
                'thumb' => $themeInfos['thumb'],
            );
        }
        return $themesList;
    }
}
