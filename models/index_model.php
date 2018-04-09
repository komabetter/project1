<?php
namespace models;

use core\Model;

class index_model extends Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function test()
    {
        $sql = " INSERT INTO TABLE1 (group_id , date) VALUES ";
        $qPart = array_fill(0, 7, "(?,?)");
        $sql .= implode(",", $qPart);
        $pstm = $this->connect->prepare($sql);
        $groupId = 1 ;
        $dateInsert = "2018-01-29";
        $date = date_create($dateInsert);
        for ($i = 1; $i <= 7; $i ++) {
            $varDate = date_format($date, "Y-m-d");
            $pstm->bindParam($i++, $groupId);
            $pstm->bindParam($i++, $groupId);
            $date = date_modify($date, "+1 day");
        }

        print_r($pstm);
    }
}
