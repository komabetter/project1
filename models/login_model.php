<?php
namespace models;

use core\Model;
use PDO;

class login_model extends Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function checkLogin($user,$pass){

        $pstm = $this->connect->prepare("SELECT password FROM TABLE2 WHERE
				emp_user = :username ");
        $pstm->execute(array(':username' => $user));
        $result = $pstm->fetch(PDO::FETCH_ASSOC);
        $passDB = null;
        $passDB = $result['emp_pass'];
        if(password_verify($pass, $passDB)){
            return true;
        }else{
            return false;
        }
    }
}
