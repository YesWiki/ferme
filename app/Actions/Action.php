<?php
namespace Ferme\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
abstract class Action
{
    protected $get;
    protected $post;
    protected $ferme;

    public function __construct($ferme, $get, $post)
    {
        $this->ferme = $ferme;
        $this->get = $get;
        $this->post = $post;
    }

    abstract public function execute();
}
