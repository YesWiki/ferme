<?php
namespace Ferme\Views;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class CsvMailing extends View
{
    /**
     * Default name for CVS exported file
     * @var string
     */
    const FILENAME = "mailing.csv";

    /**
     * Show the view
     * @return void
     */
    public function show()
    {
        $csv = new \Ferme\CSV();

        if ($this->ferme->wikis->count() <= 0) {
            $csv->printFile($this::FILENAME);
            return;
        }

        reset($this->ferme->wikis);
        foreach ($this->ferme->wikis as $wiki) {
            $infos = $wiki->getInfos();
            $csv->insert(
                array(
                    $infos['name'],
                    $infos['mail'],
                    str_replace('wakka.php?wiki=', '', $infos['url']),
                )
            );
        }

        $csv->printFile($this::FILENAME);
    }
}
