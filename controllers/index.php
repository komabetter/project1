<?php
use core\Cotroller\Controller;
use core\Session;
use models\index_model;
use models\register_model;

class index extends Controller
{

    private $model;

    public function __construct()
    {
        parent::permission();
        require_once 'models/index_model.php';
        $this->model = new index_model();
    }

    public function index()
    {
        Session::init();
        $user = Session::get('username');
        $pass = Session::get('passwrod');
        $braId = Session::get('braId');
        $this->view('index/index', [
            'username' => $user,
            'password' => $pass,
            'branch'=>$braId
        ]);
    }

    public function ipay()
    {
        $this->view('index/pay', null);
    }

    public function testpdf()
    {
        $this->view('/index/indexpdf', NULL);
    }

    public function test()
    {
        $edVisa = filter_input(INPUT_POST, 'test', FILTER_SANITIZE_STRING);
        echo $edVisa . "<br>";
        if (trim($edVisa) == "on") {
            echo "Check";
        } else {
            echo "UnCheck";
        }
    }

    public function checkScheduleStudy($st, $en, $txt)
    {
        Session::init();
        $stuArray = Session::get('stu');
        $stuId = $stuArray['stuId'];
        echo $stuId;
        $scheduleNow = $this->convertTxtToArray($txt);
        require_once 'models/register_model.php';
        $model = new register_model();
        $result = $model->getScheduleStudent($stuId);
        
        foreach ($result as $row) {
            $txt = $row['schedule'];
            $schedule = $this->convertTxtToArray($txt);
            $check = $this->checkingSchedule($scheduleNow, $schedule);
            if ($check == TRUE) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function checkingSchedule($scheduleNow, $schedule)
    {
        foreach ($scheduleNow as $now) {
            foreach ($schedule as $row) {
                
                if ($now['date'] == $row['date']) {
                    if ($now['time'] == $row['time']) {
                        echo "DateNow : " . $now['date'] . " : " . $row['date'] . "<br>";
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;
    }

    public function findStudyDay($json, $nameDay)
    {
        $nameDay = strtolower($nameDay);
        $nameDay = trim($nameDay);
        $day = "";
        switch ($nameDay) {
            case "monday":
                $day = $json->day[0]->Monday;
                break;
            case "tuesday":
                $day = $json->day[1]->Tuesday;
                break;
            case "wednesday":
                $day = $json->day[2]->Wednesday;
                break;
            case "thursday":
                $day = $json->day[3]->Thruday;
                break;
            case "friday":
                $day = $json->day[4]->Friday;
                break;
            case "saturday":
                $day = $json->day[5]->Saturday;
                break;
            case "sunday":
                $day = $json->day[6]->Sunday;
                break;
        }
        if ($day == "-") {
            return null;
        } else {
            return $day;
        }
    }

    public function convertTxtToArray($txt)
    {
        $json = json_decode($txt);
        $paramArray = array();
        $start = $json->start;
        $end = $json->end;
        while (strtotime($start) <= strtotime($end)) {
            $start = new DateTime($start);
            
            $fullDay = $start->format('l');
            
            $study = $this->findStudyDay($json, $fullDay);
            if ($study != null) {
                $date_study = $start->format('Y-m-d');
                $data = array(
                    'date' => $date_study,
                    'time' => $study
                
                );
                array_push($paramArray, $data);
            }
            $start = $start->modify('+1day');
            $start = $start->format('d-m-Y');
        }
        return $paramArray;
    }
}

