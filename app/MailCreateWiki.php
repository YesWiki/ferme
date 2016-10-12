<?php
namespace Ferme;

class MailCreateWiki extends Mail
{
    public function __construct($config, $wikiName)
    {
        $this->wikiName = $wikiName;
        $this->config = $config;
    }

    protected function getData()
    {
        $data = array(
            'wikiName' => $this->wikiName,
            'wikiUrl' => $this->config['mail_from'] . '/wikis/' . $this->wikiName,
            'to' => $this->config['mail_from'],
            'from' => $this->config['contact'],
            'subject' => 'Création du wiki ' . $this->wikiName,
            'listContacts' => $this->config['contacts']
        );

        return $data;
    }

    protected function getTemplate()
    {
        return "createWiki.twig";
    }
}
