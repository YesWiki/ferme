<?php
namespace Ferme;

/**
 * Classe DatabaseExport
 *
 * Exporte une base de donnée SQL vers un fichier
 * @package Ferme
 * @author  Florestan Bredow <florestan.bredow@supagro.fr>
 * @version 0.1.1 (Git: $Id$)
 * @copyright 2015 Florestan Bredow
 */
class Database
{
    private $dbConnexion = null;

    public function __construct($dbConnexion)
    {
        $this->dbConnexion = $dbConnexion;
    }

    public function export($file, $prefix = null)
    {
        $tableList = $this->getTableList($prefix);

        $output = "";
        foreach ($tableList as $table) {
            $output .= "DROP TABLE IF EXISTS `$table`;\n";
            $output .= $this->getCreateTable($table) . ";\n\n";
        }

        foreach ($tableList as $table) {
            $output .= $this->getTableContent($table);
        }

        file_put_contents($file, $output);
    }

    public function import($sqlFile)
    {
        $content = file_get_contents($sqlFile);
        $sth = $this->dbConnexion->prepare($content);
        $sth->execute();
    }

    private function getCreateTable($tableName)
    {
        $query = "SHOW CREATE TABLE $tableName;";
        $sth = $this->dbConnexion->prepare($query);
        $sth->execute();
        return $sth->fetchAll()[0]['Create Table'];
    }

    private function getTableContent($tableName)
    {
        $query = "SELECT * FROM $tableName;";
        $sth = $this->dbConnexion->prepare($query);
        $sth->execute();
        $queryResult = $sth->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($queryResult)) {
            return "";
        }

        $output = "LOCK TABLES `$tableName` WRITE;\n";
        $output .= "INSERT INTO `$tableName` VALUES ";

        $lineOutput = "";
        foreach ($queryResult as $line) {
            $columnOutput = "";
            foreach ($line as $columnValue) {
                $columnOutput .= "'" . $this->prepareData($columnValue) . "',";
            }
            $lineOutput .= "(" . $this->removeLastComma($columnOutput) . "),";
        }
        $output .= $this->removeLastComma($lineOutput) . ";\n";
        $output .= "UNLOCK TABLES;\n\n";
        return $output;
    }


    private function getTableList($prefix)
    {
        // Echappe les caractère % et _
        $prefix = str_replace(array('%', '_'), array('\%', '\_'), $prefix);

        $tableList = array();
        $query = "SHOW TABLES LIKE ?";
        $sth = $this->dbConnexion->prepare($query);
        $sth->execute(array($prefix . '%'));

        $result = $sth->fetchAll();

        foreach ($result as $value) {
            $tableList[$value[0]] = $value[0];
        }

        return $tableList;
    }

    private function prepareData($data)
    {
        $output = addslashes($data);
        $output = str_replace("\n", "\\n", $output);
        return $output;
    }

    private function removeLastComma($string)
    {
        if (substr($string, -1) === ',') {
            return substr($string, 0, -1);
        }
        return $string;
    }
}
