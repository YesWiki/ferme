<?php
namespace Ferme\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class DeleteArchive extends Action
{
    public function execute()
    {
        if (!isset($this->get['name'])) {
            $this->ferme->alerts->add(
                "Paramètres manquant pour la suppression de l'archive."
            );
        }

        try {
            $this->ferme->deleteArchive($this->get['name']);
        } catch (\Exception $e) {
            $this->ferme->alerts->add($e->getMessage(), 'error');
            return;
        }

        $this->ferme->alerts->add(
            "L'archive " . $this->get['name'] . " a été supprimée avec succès",
            'success'
        );
    }
}
