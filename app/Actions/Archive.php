<?php
namespace Ferme\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class Archive extends Action
{
    public function execute()
    {
        if (!isset($this->get['name'])) {
            $this->alerts->add(
                "Paramètres manquant pour créer l'archive."
            );
        }

        try {
            $this->ferme->archiveWiki($this->get['name']);
        } catch (\Exception $e) {
            $this->ferme->alerts->add($e->getMessage(), 'error');
            return;
        }

        $this->ferme->alerts->add(
            "Le wiki " . $this->get['name'] . " a été archivé avec succès.",
            'success'
        );

        $this->ferme->archives->load();
    }
}
