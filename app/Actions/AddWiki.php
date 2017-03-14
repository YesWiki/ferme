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

        if ($this->isValidWikiName($this->post['wikiName'])) {
            throw new \Exception("Ce nom n'est pas valide : "
                . "longueur entre 1 et 20 caractères, uniquement les lettres de 'A' à 'Z' "
                . "(minuscules ou majuscules), chiffres de '0' à '9', "
                . "et caractères spéciaux '_' et '-'.", 1);
        }

        if (!filter_var($this->post['mail'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Cet email n'est pas valide.", 1);
        }

        try {
            $wikiFactory = new \Ferme\WikiFactory(
                $this->ferme->config,
                $this->ferme->dbConnexion
            );
            $wikiName = $this->cleanEntry($this->post['wikiName']);
            $wiki = $wikiFactory->createNewWiki(
                $wikiName,
                $this->cleanEntry($this->post['mail']),
                $this->cleanEntry($this->post['description'])
            );
            $this->ferme->wikis->add($wikiName, $wiki);
        } catch (\Exception $e) {
            $this->ferme->alerts->add($e->getMessage(), 'error');
            return;
        }

        $this->ferme->alerts->add(
            '<a href="' . $this->ferme->config['base_url']
            . $wiki->getPath() . '">Visiter le nouveau wiki</a>',
            'success'
        );

        $mail = new \Ferme\MailCreateWiki($this->ferme->config, $this->post['wikiName']);
        $mail->send();
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

    /**
     * Définis si le nom d'un wiki est valide
     * @param  strin   $name Nom potentiel du wiki.
     * @return boolean       Vrai si le nom est valide, faux sinon
     */
    private function isValidWikiName($name)
    {
        if (preg_match("~^[a-z0-9]{1,20}$~i", $name)) {
            return false;
        }
        return true;
    }

    /**
     * Nettoie une chaine de caractère
     * @param  string $entry Chaine a nettoyer
     * @return string        Chaine de caractères nettoyées
     */
    private function cleanEntry($entry)
    {
        return htmlentities($entry, ENT_QUOTES, "UTF-8");
    }
}
