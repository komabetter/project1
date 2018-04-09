<?php
use core\Session;
use core\Cotroller\Controller;
use models\login_model;
use models\register_model;
use models\employee_model;

class login extends Controller
{

    public function __construct()
    {
        Session::init();
        $login = Session::get('loggedIn');
        if ($login == true) {
            header('location:index');
        }
    }

    public function index()
    {
        require_once 'models/register_model.php';
        $model = new register_model();
        $bra = $model->getBranch();
        $this->views('login/index', [
            'bra' => $bra
        ]);
    }

    public function login()
    {
        require_once 'models/login_model.php';
        $model = new login_model();
        $user = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $pass = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        
        if ($model->checkLogin($user, $pass)) {
            require_once 'models/employee_model.php';
            $modelEmp = new employee_model();
            $emp = $modelEmp->getEmployee();
            $braId = filter_input(INPUT_POST, 'braId', FILTER_SANITIZE_STRING);
            Session::init();
            Session::set('loggedIn', true);
            Session::set('braId', $braId);
            Session::set('emp', $emp);
            Session::set('level', $emp['emp_level_id']);
            Session::set('username', $user);
            $pass = md5($pass);
            Session::set('passwrod', $pass);
            header('location:../index');
        } else {
             header('location:../login');
        }
    }
}

