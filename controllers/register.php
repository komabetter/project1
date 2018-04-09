<?php
header('Content-Type: text/html; charset=utf-8');
use core\Session;
use core\Cotroller\Controller;
use models\register_model;
use models\invoice_model;
use models\student_model;
use models\coupon_model;

class register extends Controller
{

    private $model;

    private $regModel;

    public function __construct()
    {
        parent::permission();
        require_once 'models/student_model.php';
        require_once 'models/register_model.php';
        $this->model = new student_model();
        $this->regModel = new register_model();
    }

    public function index()
    {
        if (isset($_POST['stu_id'])) {
            $stuId = filter_input(INPUT_POST, 'stu_id', FILTER_SANITIZE_STRING);
            $result = $this->model->getStudentById($stuId);
            
            $stuName = $result['stu_firstname'] . " " . $result['stu_lastname_eng'];
            $stuNickName = $result['stu_nickname'];
            $stuTel = $result['stu_tel'];
            $stuEmail = $result['stu_email'];
            $stuNation = $result['stu_nationality'];
            $stu = array(
                'stuId' => $stuId,
                'stuName' => $stuName,
                'stuNickName' => $stuNickName,
                'stuTel' => $stuTel,
                'stuEmail' => $stuEmail,
                'stuNation' => $stuNation,
                'stu_nationality' => $stuNation
            );
            Session::init();
            Session::set('stu', $stu);
            
            require_once 'models/coupon_model.php';
            $couponModel = new coupon_model();
            $coupon = $couponModel->getCouponActive();
            Session::set('coupon', $coupon);
        }
        
        require_once 'models/register_model.php';
        $model = new register_model();
        $branch = $model->getBranch();
        $emp = $model->getEmployeeList();
        
        $this->views('register/index', [
            'bra' => $branch,
            'emp' => $emp
        ]);
    }

    public function showCourse()
    {
        Session::init();
        $stu = Session::get('stu');
        require_once 'models/register_model.php';
        $model = new register_model();
        $course = $model->getCourse();
        $result = $model->getTimeTable();
        $time = $model->getTimeTable2();
        $num_rows = 0;
        $resultCouse = null;
        
        if (isset($_POST['CouID'])) {
            
            if (trim($_POST['searchName'])) {
                $search = $_POST['searchName'];
                $resultCouse = $model->selectSubjectByName($search);
                if ($num_rows == 0) {
                    $resultCouse = $model->selectSubjectByCode($search);
                }
            } else {
                $cousId = $_POST['CouID'];
                $resultCouse = $model->selectSubject($cousId);
            }
            
            foreach ($resultCouse as $key) {
                $num_rows ++;
            }
        }
        
        $this->views('register/showCourse', [
            'courseList' => $course,
            'result' => $result,
            'time' => $time,
            'num_rows' => $num_rows,
            'resultCouse' => $resultCouse
        ]);
    }

    public function ajaxGetCourseRegister()
    {
        require_once 'models/register_model.php';
        $model = new register_model();
        $result = $model->ajaxGetCourseRegister();
        echo $result;
    }

    public function deleteReseverSubject()
    {
        Session::init();
        $arraySub = Session::get('arrSub');
        $del = $_POST['delNo'];
        unset($arraySub[$del]);
        $arraySub2 = array_values($arraySub);
        Session::unsetSession('arrSub');
        Session::set('arrSub', $arraySub2);
        
        if (count($arraySub) == 0) {
            Session::unsetSession('newTable');
            Session::unsetSession('arrSub');
        }
        header('Location: ../register/index');
    }

    public function checkScheduleStudy($st, $en, $txt)
    {
        Session::init();
        $stuArray = Session::get('stu');
        $stuId = $stuArray['stuId'];
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

    public function ReseverSubjectNew()
    {
        $couId = $_POST['CouID'];
        $couName = $_POST['CouName'];
        $couHour = $_POST['CouHour'];
        $couPrice = $_POST['CouPrice'];
        $couTool = $_REQUEST['CouTool'];
        
        echo $couPrice;
        
        if (isset($_POST['newCourse'])) {
            $txt = $_POST['schedule'];
            $st = $_POST['startDate'];
            $en = $_POST['endDate'];
            
            $result = $this->checkScheduleStudy($st, $en, $txt);
            if ($result == TRUE) {
                echo '<script type="text/javascript">alert("เวลาเรียนตรงกับใบลงทะเบียนก่อนหน้า");location="/register/showCourse";</script>';
                return 0;
            } else {
                $schedule = Session::get('newTable');
                if ($schedule != null) {
                    $data = array(
                        'newTable' => $txt,
                        'stDate' => $st,
                        'enDate' => $en
                    );
                    array_push($schedule, $data);
                } else {
                    $schedule = array();
                    $data = array(
                        'newTable' => $txt,
                        'stDate' => $st,
                        'enDate' => $en
                    );
                    array_push($schedule, $data);
                }
                Session::unsetSession('newTable');
                Session::set('newTable', $schedule);
            }
        }
        $sub = array(
            'CouID' => $couId,
            'CouName' => $couName,
            'CouHour' => $couHour,
            'CouPrice' => $couPrice,
            'CouTool' => $couTool,
            'NewCourse' => "Y"
        );
        $arraySub = array();
        array_push($arraySub, $sub);
        $sessionArrSub = Session::get('arrSub');
        if ($sessionArrSub != null) {
            $arraySub = Session::get('arrSub');
            $n = 1;
            foreach ($arraySub as $result) { // check same id course
                $couId = trim($couId);
                $couIdArr = trim($result['CouID']);
                $n = strcmp($couId, $couIdArr);
                if ($n == 0) {
                    echo '<script type="text/javascript">alert("ไม่สามารถเลือกรายวิชาซ้ำกันได้");location="../register/index";</script>';
                }
            } // end foreach
            if ($n != 0) {
                array_push($arraySub, $sub);
                Session::set('arrSub', $arraySub);
            }
        } else {
            Session::set('arrSub', $arraySub);
        }
        $test = Session::get('arrSub');
        header('Location: ../register/index');
    }

    public function saveRegister()
    {
        require_once 'models/register_model.php';
        $model = new register_model();
        $result = $model->saveRegister();
        if ($result != null) {
            require_once 'models/invoice_model.php';
            $invModel = new invoice_model();
            $regId = $result['regId'];
            $totalPrice = $result['totalPrice'];
            $stu_code = $result['stuCode'];
            $emp_id = $result['empId'];
            $bra_id = $result['braId'];
            $totalHour = $result['totalHour'];
            $invModel->createInvoice($regId, $totalPrice, $stu_code, $emp_id, $bra_id);
            $this->pdfRegister($regId, $totalHour);
        } else {}
    }

    public function confirmCourse()
    {
        $couId = filter_input(INPUT_POST, 'CouID', FILTER_SANITIZE_STRING);
        $couName = filter_input(INPUT_POST, 'CouName', FILTER_SANITIZE_STRING);
        $couHour = filter_input(INPUT_POST, 'CouHour', FILTER_SANITIZE_STRING);
        $couPrice = filter_input(INPUT_POST, 'CouPrice', FILTER_SANITIZE_STRING);
        $couTool = filter_input(INPUT_POST, 'CouTool', FILTER_SANITIZE_STRING);
        $pricePerHours = filter_input(INPUT_POST, 'PricerPerHours', FILTER_SANITIZE_STRING);
        
        $this->views('register/confirmCourse', [
            'coudId' => $couId,
            'couName' => $couName,
            'couHour' => $couHour,
            'couPrice' => $couPrice,
            'couTool' => $couTool,
            'pricerPerHours' => $pricePerHours
        ]);
    }

    public function registerList()
    {
        $id = filter_input(INPUT_POST, 'registerId', FILTER_SANITIZE_STRING);
        if (isset($id)) {
            if ($id == 1) {
                $this->views('register/registerListAll', NULL);
            }
        } else {
            $this->views('register/registerList', NULL);
        }
    }

    public function getRegisterList($id)
    {
        require_once 'models/register_model.php';
        $model = new register_model();
        $list = $model->getAllRegiser($id);
        return $list;
    }

    public function formRegister()
    {
        require_once 'models/register_model.php';
        $model = new register_model();
        
        if (isset($_POST['stuId'])) {
            $stuId = filter_input(INPUT_POST, 'stuId', FILTER_SANITIZE_STRING);
            $regId = $model->getLastRegIdByStudentId($stuId);
            if ($regId == null) {
                echo '<script type="text/javascript">alert("ไม่มีใบลงทะเบียนล่าสุด");location="../student";</script>';
            }
            
            $stu = $model->getStudentFromRegId($regId);
            $result = $model->getRegisterDetail($regId);
        } else if (isset($_GET['regId'])) {
            $stu = $model->getStudentFromRegId($regId);
            $result = $model->getRegisterDetail($regId);
        } else {
            if (isset($_POST['regId'])) {
                $regId = filter_input(INPUT_POST, 'regId', FILTER_SANITIZE_STRING);
                $stu = $model->getStudentFromRegId($regId);
                $result = $model->getRegisterDetail($regId);
            } else {
                header('location:../register/registerList');
            }
        }
        $totalHour = 0;
        foreach ($result as $row){
            $totalHour += $row['Reg-hour'];
        }

        require_once 'models/invoice_model.php';
        $invModel = new invoice_model();
        $emp = $invModel->getEmployeeSale($regId);
        $coupon = $model->getRegisterByRegId($regId);
        $this->views('register/form_register', [
            'result' => $result,
            'stu' => $stu,
            'regId' => $regId,
            'emp' => $emp,
            'coupon' => $coupon,
            'purchasedHour' => $totalHour
        ]);
    }

    public function cancelRegister()
    {
        require_once 'models/register_model.php';
        $model = new register_model();
        $regId = filter_input(INPUT_POST, 'regId', FILTER_SANITIZE_STRING);
        if ($model->cancelRegister($regId)) {
            echo '<script type="text/javascript">alert("ยกเลิกใบลงทะเบียนเสร็จสิ้น");location="../register/registerList";</script>';
        } else {
            echo '<script type="text/javascript">alert("มีบ้างอย่างผืดพลาด กรุณาลองอีกครั้ง");location="../register/registerList";</script>';
        }
    }

    public function editRegister()
    {
        Session::init();
        $regId = Session::get('regId');
        if ($regId == null) {
            $regId = filter_input(INPUT_POST, 'regId', FILTER_SANITIZE_STRING);
        }
        Session::unsetSession('regId');
        require_once 'models/register_model.php';
        $model = new register_model();
        $result = $model->registerDetail($regId);
        $branch = $model->getBranch();
        require_once 'models/student_model.php';
        $stuModel = new student_model();
        $stuId = 0;
        $createDate = "";
        $note = "";
        $braId = "";
        $empId = "";
        foreach ($result as $row) {
            $stuId = $row['St_ID'];
            $createDate = $row['Reg-date'];
            $note = $row['Reg-Note'];
            $braId = $row['Bra-id'];
            $empId = $row['emp_sale'];
        }
        $stu = $stuModel->getStudentById($stuId);
        $emp = $model->getEmployeeList();
        $this->views('register/edit_register', [
            'regDetail' => $result,
            'bra' => $branch,
            'stu' => $stu,
            'date' => $createDate,
            'emp' => $emp,
            'note' => $note,
            'braId' => $braId,
            'empId' => $empId,
            'regId' => $regId
        ]);
    }

    public function updateRegister()
    {
        require_once 'models/register_model.php';
        $model = new register_model();
        $model->updateRegister();
        $regId = filter_input(INPUT_POST, 'regId', FILTER_SANITIZE_STRING);
        Session::init();
        Session::set('regId', $regId);
        echo '<script type="text/javascript">alert("ทำรายการเสร็จสิ้น");location="../register/editRegister";</script>';
    }

    public function pdfRegister($regId, $totalHour)
    {
        require_once 'models/register_model.php';
        $model = new register_model();
        
        $stu = $model->getStudentFromRegId($regId);
        $result = $model->getRegisterDetail($regId);
        
        require_once 'models/invoice_model.php';
        $invModel = new invoice_model();
        $emp = $invModel->getEmployeeSale($regId);
        
        $this->views('register/pdf_register_new', [
            'result' => $result,
            'stu' => $stu,
            'regId' => $regId,
            'emp' => $emp,
            'purchasedHour' => $totalHour
        ]);
    }

    public function registerListAll()
    {
        $result = $this->regModel->RegisterListAllThisYear();
        $this->views('/register/registerList', [
            'list' => $result
        ]);
    }
    
    public function registerListComplete()
    {
        $result = $this->regModel->RegisterListCompleteThisYear();
        $this->views('/register/registerList', [
            'list' => $result
        ]);
    }
    
    public function registerListUncomplete()
    {
        $result = $this->regModel->RegisterListUncompleteThisYear();
        $this->views('/register/registerList', [
            'list' => $result
        ]);
    }
}

