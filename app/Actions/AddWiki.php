<?php
namespace Ferme\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class AddWiki extends Action
{
    public function execute()
    {
        if (!$this->isHashcashValid()) {
            $this->ferme->alerts->add(
                'La plantation de wiki est une activité délicate qui'
                . ' ne doit pas être effectuée par un robot. (Pensez à'
                . ' activer JavaScript)',
                'error'
            );
            return;
        }

        if (!isset($this->post['wikiName'])
            or !isset($this->post['mail'])
            or !isset($this->post['description'])
        ) {
            $this->ferme->alerts->add("Formulaire incomplet.", 'error');
            return;
        }

        try {
            $wikiPath = $this->ferme->createWiki(
                $this->post['wikiName'],
                $this->post['mail'],
                $this->post['description']
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

        $mail = new \Ferme\MailCreateWiki($this->ferme->config, $this->post['wikiName']);
        $mail->send();

        $this->ferme->wikis->load();
    }

    private function isHashcashValid()
    {
        require_once 'app/secret/wp-hashcash.php';
        if (!isset($this->post["hashcash_value"])
            || hashcash_field_value() != $this->post["hashcash_value"]) {
            return false;
        }
        return true;
    }
}
