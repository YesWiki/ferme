<?php
namespace Ferme;

abstract class Mail
{
    abstract protected function getTemplate();
    abstract protected function getData();

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function send()
    {
        $themePath = 'themes/' . $this->config['template'] . '/mails/';
        $twigLoader = new \Twig_Loader_Filesystem($themePath);
        $twig = new \Twig_Environment($twigLoader);
        $data = $this->getData();
        $content = $twig->render($this->getTemplate(), $data);

        mail(
            $data['to'],
            $data['subject'],
            $content
            //"From:" . $data['from']
        );
    }
}
