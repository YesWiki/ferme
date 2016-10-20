<?php
namespace Ferme\Views;

class Home extends TwigView
{

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
     * Génère l'URL vers le javascript qui calcule le hash
     * @return string URL vers le javascript
     */
    private function hashCash()
    {
        $hashcashUrl =
        'app/wp-hashcash-js.php?siteurl='
        . $this->ferme->config['base_url'];

        return $hashcashUrl;
    }

    /**
     * Retourne la liste des thèmes.
     * @return array tableau de tableau avec deux clés : name et thumb
     */
    private function getThemesList()
    {
        $themesList = array();

        include "packages/"
            . $this->ferme->config['source']
            . "/install.config.php";

        foreach ($config['themes'] as $key => $value) {
            $themesList[] = array(
                'name' => $key,
                'thumb' => $value['thumb'],
            );
        }
        return $themesList;
    }
}
