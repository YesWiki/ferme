<?php
namespace Ferme;

class MailCreateWiki extends Mail
{
    public function __construct($ferme, $wikiName)
    {
        $this->wikiName = $wikiName;
        $this->ferme = $ferme;
    }

    protected function getData()
    {
        $data = array(
            'wikiName' => $this->wikiName,
            'wikiUrl' => $this->ferme->config['mail_from'] . '/wikis/' . $this->wikiName,
            'to' => $this->ferme->config['mail_from'],
            'from' => $this->ferme->config['contact'],
            'subject' => 'Création du wiki ' . $this->wikiName,
            'listContacts' => $this->ferme->config['contacts']
        );

        return $data;
    }

    protected function getTemplate()
    {
        return "createWiki.twig";
    }
}
