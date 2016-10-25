<?php
namespace Ferme\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class UpdateConfiguration extends Action
{
    public function execute()
    {
        if (!isset($this->get['name'])) {
            $this->ferme->alerts->add(
                "Paramètres manquant pour mettre à jour la configuration."
            );
        }

        try {
            $this->ferme->updateConfiguration($this->get['name']);
        } catch (\Exception $e) {
            $this->ferme->alerts->add($e->getMessage(), 'error');
            return;
        }

        $this->ferme->alerts->add(
            "La configuration de " . $this->get['name'] . " a été mise à "
            . "jour avec succès.",
            'success'
        );

    }
}
