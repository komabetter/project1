<?php
namespace core;

use PDO;

class Model
{

    protected $connect;

    public function __construct()
    {
        $this->connect = new Database();
        $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connect->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        //TEST

    }
}

