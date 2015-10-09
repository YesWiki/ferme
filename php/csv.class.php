<?php
namespace Ferme;

/**
 * Classe CSV
 *
 * Stock un tableau et exporte un fichier CSV a partir de celui-ci.
 * @package Ferme
 * @author  Florestan Bredow <florestan.bredow@supagro.fr>
 * @version 0.1.1 (Git: $Id$)
 * @copyright 2015 Florestan Bredow
 */
class CSV
{

    private $csv = null;

    /**
     * Add a new line to csv file.
     *
     * @param array $line list of row cell
     */
    public function insert($row)
    {
        $string = "";
        foreach ($row as $cell) {
            // corrige le bug de l'export avec des intitulés contenant des
            // guillemet.
            $cell = str_replace('"', '""', $cell);
            $string .= '"' . $cell . '",';
        }
        $this->csv .= $string . "\n";
    }

    /**
     * Make CSV data from array
     *
     * @param array $array Array to transform.
     */
    public function array2CSV($array)
    {
        foreach ($array as $row) {
            $this->insert($row);
        }
    }

    /**
     * fonction de sortie du fichier avec un nom spécifique.
     *
     * @param string $filename Nom du fichier CSV a exporter
     */
    public function printFile($filename)
    {
        header("Content-type: text/CSV");
        header("Content-disposition: attachment; filename=" . $filename . ".csv");
        print $this->csv;
        exit;
    }
}
