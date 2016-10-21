<?php
namespace Ferme\Views;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
abstract class View
{

    /**
     * Model, to grab informations.
     * @var \Ferme\Ferme
     */
    protected $ferme;

    /**
     * Constructor
     * @param \Ferme\Ferme $ferme reference to model.
     */
    public function __construct($ferme)
    {
        $this->ferme = $ferme;
    }

    /**
     * Show the view
     * @return void
     */
    abstract public function show();

}
