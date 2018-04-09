<?php
namespace core;

use PDO;
use PDOException;

class Database extends \PDO

{

    public function __construct()
    {
        try {
            parent::__construct(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
            ));
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
            header('Location:/help/databaseWorng');
        }
        
    }
}

