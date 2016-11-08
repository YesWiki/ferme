<?php
namespace Ferme;

class Cache {

    /**
     * Constructor
     * @param Configuration $config      [description]
     * @param [type] $dbConnexion [description]
     */
    public function __construct($config, $dbConnexion)
    {
        $this->dbConnexion = $dbConnexion;
        $this->config = $config;
        $this->initCacheTable();
    }

    /**
     * Write value in cache
     * @param  [type] $ressourceID [description]
     * @param  array $data        [description]
     * @return bool                true if ok so false
     */
    public function write($ressourceID, $data)
    {
        $jsonedData = json_encode($data);
        $queryDelete = "DELETE FROM cache WHERE `id` = :ressourceID ;
            INSERT INTO cache (`id`, `timestamp`, `data`)
                VALUES (:ressourceID, CURRENT_TIMESTAMP, :jsonedData);";

        $sth = $this->dbConnexion->prepare($queryDelete);
        $sth->bindParam(':ressourceID', $ressourceID, \PDO::PARAM_STR);
        $sth->bindParam(':jsonedData', $jsonedData, \PDO::PARAM_STR);
        return $sth->execute();
    }

    /**
     * Get value from cache.
     * @param  string $ressourceID  Cache ID
     * @return array                Information stored for this ID
     */
    public function read($ressourceID)
    {
        $query = "SELECT * FROM `cache` WHERE `id` = :ressourceID";
        $sth = $this->dbConnexion->prepare($query);
        $sth->bindParam(':ressourceID', $ressourceID, \PDO::PARAM_STR);
        $sth->execute();

        $queryResult = $sth->fetch();

        // There is nothing in cache
        if (empty($queryResult)) {
            return false;
        }

        // Cache is too old.
        $tsCacheData = strtotime($queryResult['timestamp']);
        if (($tsCacheData - time()) > 3600) {
            return false;
        }

        return json_decode($queryResult['data'], true);
    }

    /**
     * Empty the cache
     */
    public function clear()
    {
        $query = "TRUNCATE `cache`";
        $sth = $this->dbConnexion->prepare($query);
        $sth->execute();
    }

    /**
     * Make table if not exist
     * @return [type] [description]
     */
    private function initCacheTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS `cache` (
            `id` VARCHAR(30) NOT NULL ,
            `timestamp` TIMESTAMP NOT NULL ,
            `data` TEXT NOT NULL ,
            PRIMARY KEY (`id`)
        );";
        $sth = $this->dbConnexion->prepare($query);
        $sth->execute();
    }
}
