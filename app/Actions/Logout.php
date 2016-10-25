<?php
namespace Ferme\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class Logout extends Action
{
    public function execute()
    {
        $this->ferme->users->logout();
    }
}
