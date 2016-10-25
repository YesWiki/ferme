<?php
namespace Ferme\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
abstract class AddWiki extends Action
{
    public function execute()
    {
        if (!$this->isHashcashValid($post)) {
            $this->ferme->alerts->add(
                'La plantation de wiki est une activité délicate qui'
                . ' ne doit pas être effectuée par un robot. (Pensez à'
                . ' activer JavaScript)',
                'error'
            );
            return;
        }

        if (!isset($post['wikiName'])
            or !isset($post['mail'])
            or !isset($post['description'])
        ) {
            $this->ferme->alerts->add("Formulaire incomplet.", 'error');
            return;
        }

        try {
            $wikiPath = $this->ferme->createWiki(
                $post['wikiName'],
                $post['mail'],
                $post['description']
            );
        } catch (\Exception $e) {
            $this->ferme->alerts->add($e->getMessage(), 'error');
            return;
        }

        $this->ferme->alerts->add(
            '<a href="' . $this->ferme->config['base_url']
            . $wikiPath . '">Visiter le nouveau wiki</a>',
            'success'
        );

        $mail = new MailCreateWiki($this->ferme->config, $post['wikiName']);
        $mail->send();

        $this->ferme->wikis->load();
    }

    private function isHashcashValid($post)
    {
        require_once 'app/secret/wp-hashcash.php';
        if (!isset($post["hashcash_value"])
            || hashcash_field_value() != $post["hashcash_value"]) {
            return false;
        }
        return true;
    }
}
