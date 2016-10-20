<?php
namespace Ferme\Views;

class CsvMailing extends View
{
    const FILENAME = "mailing.csv";

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
