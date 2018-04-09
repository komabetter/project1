<?php
namespace core\Cotroller;

use core\Session;

class Controller
{

    public function __construct()
    {
        header('Content-type: text/html; charset=utf-8');
    }

    public function permission()
    {
        Session::init();
        $login = Session::get('loggedIn');
        if ($login == false) {
            Session::destroy();
            header('location:login');
            exit();
        }
    }

    public function logout()
    {
        Session::init();
        Session::destroy();
        header('location:/login');
    }

    public function model($model)
    {
        require_once 'models/' . $model . '.php';
        return new $model();
    }

    public function view($view, $data)
    {
        require_once 'views/' . $view . '.php';
    }

    public function views($view, $data = [])
    {
        require_once 'views/' . $view . '.php';
    }

    public function viewStudentList($view, $data, $dataApprove)
    {
        require_once 'views/' . $view . '.php';
    }

    public function Calculation_of_credit_balances($pay_formatt, $pay_amountt)
    {
        $pay_format = $pay_formatt;
        $pay_amount = $pay_amountt;
        $amount_return = 0;
        $text_test_card = mb_substr($pay_format, 0, 4, 'UTF-8');
        if ($text_test_card == "บัตร" || $text_test_card == "Alip") {
            $total_amount = 0;
            if ($pay_format == "บัตรเครดิต(รูดเครื่องกรุงเทพ)") { // หัก 1.6%
                $total_amount = $pay_amount - (($pay_amount * 0.016) + (($pay_amount * 0.016) * 0.07));
            } else if ($pay_format == "บัตรเครดิต(รูดเครื่องกสิกร)") { // หัก 1.75%
                $total_amount = $pay_amount - (($pay_amount * 0.0175) + (($pay_amount * 0.0175) * 0.07));
            } else if ($pay_format == "บัตรเครดิต(รูดเครื่องกรุงไทย)") { // หัก 1.3%
                $total_amount = $pay_amount - (($pay_amount * 0.013) + (($pay_amount * 0.013) * 0.07));
            } else if ($pay_format == "บัตรเครดิต(รูดเครื่องกรุงเทพ ผ่อน 0% 3เดือน)") { // หัก 0.8%*3=2.4 และค่าธรรมเนียอีก 1.6%
                $p1 = 0;
                $p2 = 0;
                $p1 = (($pay_amount * 0.016) + (($pay_amount * 0.016) * 0.07)); // ค่าทำเนียมการใช้วงเงิน
                $p2 = (($pay_amount * 0.024) + (($pay_amount * 0.024) * 0.07)); // ค่าทำเนียมการผ่อน
                $total_amount = $pay_amount - ($p2 + $p1);
            } else if ($pay_format == "บัตรเครดิต(รูดเครื่องกรุงเทพ ผ่อน 0% 6เดือน)") { // หัก 0.8%*6=4.8 และค่าธรรมเนียอีก 1.6%
                $p1 = 0;
                $p2 = 0;
                $p1 = (($pay_amount * 0.016) + (($pay_amount * 0.016) * 0.07)); // ค่าทำเนียมการใช้วงเงิน
                $p2 = (($pay_amount * 0.048) + (($pay_amount * 0.048) * 0.07)); // ค่าทำเนียมการผ่อน
                                                                                // $total_amount=$pay_amount-1602.43;
                $total_amount = $pay_amount - ($p2 + $p1);
            } else if ($pay_format == "บัตรเครดิต(รูดเครื่องกสิกร ผ่อน 0% 3เดือน)") { // หัก 0.8%*3=2.4
                $total_amount = $pay_amount - (($pay_amount * 0.024) + (($pay_amount * 0.024) * 0.07)); // ค่าทำเนียมการผ่อน
            } else if ($pay_format == "บัตรเครดิต(รูดเครื่องกสิกร ผ่อน 0% 6เดือน)") { // หัก 0.8%*6=4.8
                $total_amount = $pay_amount - (($pay_amount * 0.048) + (($pay_amount * 0.048) * 0.07)); // ค่าทำเนียมการผ่อน
            } else if ($pay_format == "บัตรเครดิต(รูดเครื่องกรุงเทพ  Union Pay)") {
                $total_amount = $pay_amount - (($pay_amount * 0.0055) + (($pay_amount * 0.0055) * 0.07));
            } else if ($pay_format == "Alipay") {
                
                $pay_cal = $pay_amount - ($pay_amount * 0.982);
                $total_amount = $pay_amount - $pay_cal;
            }
            
            $amount_return = number_format($total_amount, 2, '.', '');
        } else {
            $amount_return = $pay_amountt;
        }
        return $amount_return;
    }
}

?>