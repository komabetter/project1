<?php
namespace models;
use core\Model;
use core\Session;
use Exception;
use PDO;

class register_model extends Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getBranch()
    {
        $pstm = $this->connect->prepare("SELECT * FROM TABLE7");
        $pstm->execute();
        return $pstm->fetchAll();
    }

    public function getEmployeeList()
    {
        $pstm = $this->connect->prepare("SELECT * FROM TABLE3 WHERE emp_status = 'Y' ;");
        $pstm->execute();
        return $pstm->fetchAll();
    }

    public function getCourse()
    {
        $pstm = $this->connect->prepare("SELECT * FROM TABLE4 ORDER BY SubID ASC ;");
        $pstm->execute();
        return $pstm->fetchAll();
    }

    public function getTimeTable()
    {
        $pstm = $this->connect->prepare("SELECT * FROM TABLE5 ;");
        $pstm->execute();
        return $pstm->fetchAll();
    }

    public function getTimeTable2()
    {
        $pstm = $this->connect->prepare("SELECT * FROM TABLE5 GROUP BY course_time ORDER BY course_time");
        $pstm->execute();
        return $pstm->fetchAll();
    }

    public function selectSubjectByName($search)
    {
        $pstm = $this->connect->prepare("SELECT * FROM TABLE6 WHERE CouName LIKE CONCAT('%',:search,'%') ;");
        $pstm->execute(array(
            ':search' => $search
        ));
        return $pstm->fetchAll();
    }

    public function selectSubjectByCode($search)
    {
        $pstm = $this->connect->prepare("SELECT * FROM TABLE6 WHERE CouID LIKE CONCAT('%',:search,'%') ; ");
        $pstm->execute(array(
            ':search' => $search
        ));
        return $pstm->fetchAll();
    }

    public function selectSubject($cousId)
    {
        if($cousId == "L01"){
            $sql = "SELECT * FROM TABLE6 WHERE CouID LIKE CONCAT(:search,'%') AND CouLeID = 'SP' OR CouID = 'L01BGWK01G';";
        }else{
            $sql = "SELECT * FROM TABLE6 WHERE CouID LIKE CONCAT(:search,'%') AND CouLeID = 'SP' ;";
        }
        $pstm = $this->connect->prepare($sql);
        $pstm->execute(array(
            ':search' => $cousId
        ));
        return $pstm->fetchAll();
    }

    public function ajaxGetCourseRegister()
    {
        $nation = filter_input(INPUT_GET, 'nation', FILTER_SANITIZE_STRING);
        $couId = filter_input(INPUT_GET, 'couId', FILTER_SANITIZE_STRING);
        $nation = strtolower($nation);
        if ($nation == "thai") {
            $sql = "SELECT * FROM TABLE14,TABLE15
                    WHERE TABLE14.type_id = TABLE15.type_id
                    AND course_id = :couId ;";
        } else {
            $sql = "SELECT * FROM TABLE14,price_detail_eng
                    WHERE TABLE14.type_id = price_detail_eng.type_id
                    AND course_id = :couId ;";
        }

        $pstm = $this->connect->prepare($sql);
        $pstm->execute(array(
            ':couId' => $couId
        ));
        $result = $pstm->fetch(PDO::FETCH_ASSOC);

        $typeId = $result['type_id'];
        $newType = $result['type_new'];

        $newType = strtolower($newType);

        if ($newType == 'n') {
            $sql = "SELECT * FROM TABLE10 WHERE type_id = :typeId ORDER BY course";
        } else {
            $sql = "SELECT * FROM TABLE11 WHERE type_id = :typeId ORDER BY week";
        }

        $pstm = $this->connect->prepare($sql);
        $pstm->execute(array(
            ':typeId' => $typeId
        ));
        $packageResult = $pstm->fetchAll();

        $result = array();
        $data = array(
            "newType" => $newType
        );
        array_push($result, $data);
        $newType = strtolower($newType);

        if ($newType == "n") {
            foreach ($packageResult as $key) {
                $data = array(
                    "id" => $key['id'],
                    "course" => $key['course'],
                    "hours" => $key['hours'],
                    "pricePerHours" => $key['price_per_hours'],
                    "price" => $key['price'],
                    "discount" => $key['discount'],
                    "materials" => $key['materials'],
                    "total" => $key['total']

                );
                array_push($result, $data);
            }
        } else {
            foreach ($packageResult as $key) {
                $data = array(
                    "id" => $key['id'],
                    "week" => $key['week'],
                    "towDay" => $key['2day'],
                    "threeDay" => $key['3day'],
                    "fourDay" => $key['4day'],
                    "fiveDay" => $key['5day'],
                    "sixDay" => $key['6day'],
                    "sevenDay" => $key['7day'],
                    "materials" => $key['materials'],
                    "discount" => $key['discount']

                );
                array_push($result, $data);
            }
        }

        return json_encode($result);
    }

    public function getLastIdRegisterBeforInsert()
    {
        $sql = "SELECT Reg_ID FROM TABLE9 ORDER BY Reg_ID DESC LIMIT 1;";
        $pstm = $this->connect->prepare($sql);
        $pstm->execute();
        $row = $pstm->fetch(PDO::FETCH_ASSOC);
        $id = substr($row['Reg_ID'], 1);
        $id ++;
        $id = str_pad($id, 6, '0', STR_PAD_LEFT);
        $id = "R" . $id;
        return $id;
    }

    public function getLastRegIdAuto()
    {
        $sql = "SELECT Reg_ID FROM TABLE9 ORDER BY Reg_ID DESC LIMIT 1;";
        $pstm = $this->connect->prepare($sql);
        $pstm->execute();
        $row = $pstm->fetch(PDO::FETCH_ASSOC);
        $id = (string) $row['Reg_ID'];
        $id = substr($row['Reg_ID'], 1);
        $id = ltrim($id, '0');
        $id += 1;
        return $id;
    }

    public function getLastIdRegisterAfterInsert()
    {
        $sql = "SELECT Reg_ID FROM TABLE9 ORDER BY Reg_ID DESC LIMIT 1;";
        $pstm = $this->connect->prepare($sql);
        $pstm->execute();
        $row = $pstm->fetch(PDO::FETCH_ASSOC);
        $id = (string) $row['Reg_ID'];
        return $id;
    }

    public function saveRegister()
    {
        Session::init();
        $arrSub = Session::get('arrSub');
        $stu_code = filter_input(INPUT_POST, 'stu_code', FILTER_SANITIZE_STRING);
        $bra_id = filter_input(INPUT_POST, 'bra_id', FILTER_SANITIZE_STRING);
        $emp_id = filter_input(INPUT_POST, 'emp_id', FILTER_SANITIZE_STRING);
        $branch = filter_input(INPUT_POST, 'branch', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $discount = filter_input(INPUT_POST, 'coupon_id', FILTER_SANITIZE_STRING);
        $regDetAuto = "";
        $totalPrice = 0;
        $totalHour = 0;
        foreach ($arrSub as $result) {
            $totalPrice += ($result['CouPrice']);
            $totalHour += $result['CouHour'];
        }

        $discount = explode("/", $discount);
        if ($discount != 0) {
            $totalPrice = $totalPrice - ($totalPrice * ($discount[1] / 100));
        }

        $indexNewTable = 0;
        if ($this->insertRegister($totalPrice, $stu_code, $bra_id, $emp_id, $discount[0],$totalHour)) {
            $regId = $this->getLastIdRegisterAfterInsert();
            $newTable = Session::get('newTable');
            foreach ($arrSub as $result) {
                $this->insertRegisterDetail($regId, $result['CouID'], $result['CouName'], $result['CouHour'], $result['CouPrice'], $result['CouTool']);

                $couId = $result['NewCourse'];
                $regDetAuto = $this->getRegDetAuto($result['CouID'], $regId);
                if ($couId == "Y") {
                    $couId = $result['CouID'];

                    if ($newTable != null) {
                        $regDetAuto = $this->getRegDetAuto($result['CouID'], $regId);
                        $this->insertScheduleStudent($stu_code, $regId, $newTable[$indexNewTable]['stDate'], $newTable[$indexNewTable]['enDate'], $newTable[$indexNewTable]['newTable'], $regDetAuto);
                        $indexNewTable ++;
                    }
                    Session::unsetSession('newTable');
                }
            }
            foreach ($branch as $bra) {
                $this->insertRegisterActiveBranch($regId, $bra);
            }

            Session::unsetSession('arrSub');
            return array(
                'regId' => $regId,
                'totalPrice' => $totalPrice,
                'stuCode' => $stu_code,
                'empId' => $emp_id,
                'braId' => $bra_id,
                'totalHour'=>$totalHour
            );
        }else{

        }
        return null;
    }

    public function insertRegisterActiveBranch($regId, $bra)
    {
        $sql = "INSERT INTO TABLE8 (`Reg_ID`,`Bra_ID`,`Reg_Act_Bra_Status`) VALUES (:regId,:bra,'Y') ;";
        $pstm = $this->connect->prepare($sql);
        $pstm->execute(array(
            ':regId' => $regId,
            ':bra' => $bra
        ));
    }

    public function insertRegister($totalPrice, $stu_code, $bra_id, $emp_id, $couponId,$totalHour)
    {
        $coupon = filter_input(INPUT_POST, 'coupon_number', FILTER_SANITIZE_STRING);
        $date_create = filter_input(INPUT_POST, 'date_create', FILTER_SANITIZE_STRING);
        $stu_status = filter_input(INPUT_POST, 'stu_status', FILTER_SANITIZE_STRING);
        $stu_name = filter_input(INPUT_POST, 'stu_name', FILTER_SANITIZE_STRING);
        $text = "รอใบแจ้งหนี้";
        $regId = $this->getLastIdRegisterBeforInsert();
        $regIdAuto = $this->getLastRegIdAuto();


        $sql = "INSERT INTO TABLE9 (`Reg_ID_Auto`,`Reg_ID`,`St_ID`,`Em_ID`,`Reg_status`,`Bra-id`,`Reg-sum`,`Reg-Note`,`emp_sale`,`s_status`,`Reg-date`,`expired_date`,`coupon_id`,`Reg-hour`)
                VALUES (:regIdAuto,:regId,:stuId,:empId,:text,:braId,:regSum,:regNote,:empSale,:stuStatus,NOW(),DATE_ADD(NOW(), INTERVAL 7 DAY),:couponId,:totalHour) ;";

        try{
        $pstm = $this->connect->prepare($sql);
        $result = $pstm->execute(array(
            ':regIdAuto' => $regIdAuto,
            ':regId' => $regId,
            ':stuId' => $stu_code,
            ':empId' => $emp_id,
            ':text' => $text,
            ':braId' => $bra_id,
            ':regSum' => $totalPrice,
            ':regNote' => $coupon,
            ':empSale' => $emp_id,
            ':stuStatus' => $stu_status,
            ':couponId' => $couponId,
            ':totalHour'=>$totalHour
        ));
        }catch(Exception $e){
            echo 'Exception -> ';
            var_dump($e->getMessage());
        }
        return $result;
    }

    public function insertRegisterDetail($regId, $CouID, $CouName, $CouHour, $CouPrice, $CouMater)
    {
        $sql = "INSERT INTO TABLE12 (`Reg-ID`,`Cou-ID`,`Cou-name`,`Cou-hour`,`Cou-price`,`Cou-price-real`,`materials`)
                 VALUES (:regId,:couId,:couName,:couHour,:couPrice,:couPrice2,:couMater) ;";
        $pstm = $this->connect->prepare($sql);
        $pstm->bindValue(':regId', $regId,PDO::PARAM_STR);
        $pstm->bindValue(':couId', $CouID,PDO::PARAM_STR);
        $pstm->bindValue(':couName', $CouName,PDO::PARAM_STR);
        $pstm->bindValue(':couHour', $CouHour,PDO::PARAM_STR);
        $pstm->bindValue(':couPrice', $CouPrice,PDO::PARAM_STR);
        $pstm->bindValue(':couPrice2', $CouPrice,PDO::PARAM_STR);
        $pstm->bindValue(':couMater', $CouMater,PDO::PARAM_STR);
        $pstm->execute();
    }

    public function insertScheduleStudent($stuId, $regId, $stDate, $endDate, $schedule, $regDetAuto)
    {
        $pstm = $this->connect->prepare("INSERT INTO student_schedule (stu_id,reg_id,`start_date`,`end_date`,`schedule`,`reg_det_auto`)  VALUES (:stuId,:regId,:startDate,:endDate,:schedule,:regDet);");
        $pstm->execute(array(
            ':stuId' => $stuId,
            ':regId' => $regId,
            ':startDate' => $stDate,
            ':endDate' => $endDate,
            ':schedule' => $schedule,
            ':regDet' => $regDetAuto
        ));
    }

    public function getAllRegiser($id)
    {

        if($id == 1){
            $sql = "SELECT * FROM TABLE9
                LEFT JOIN TABLE13 ON TABLE9.St_ID = TABLE13.stu_id
                ORDER BY TABLE9.Reg_ID DESC";
        }else if($id == 2){
            $sql = "SELECT * FROM TABLE9
                LEFT JOIN TABLE13 ON TABLE9.St_ID = TABLE13.stu_id
                WHERE Reg_status = 'สมบูรณ์'
                ORDER BY TABLE9.Reg_ID DESC";
        }else if($id == 3){
            $sql = "SELECT * FROM TABLE9
                LEFT JOIN TABLE13 ON TABLE9.St_ID = TABLE13.stu_id
                WHERE Reg_status != 'สมบูรณ์'
                ORDER BY TABLE9.Reg_ID DESC";
        }

        $pstm = $this->connect->prepare($sql);
        $pstm->execute();
        return $pstm->fetchAll();
    }

    public function getLastRegIdByStudentId($stuId)
    {
        $sql = "SELECT * FROM TABLE9 WHERE St_ID = :stuId ORDER BY TABLE9.Reg_ID DESC LIMIT 1";
        $pstm = $this->connect->prepare($sql);
        $pstm->execute(array(
            ':stuId' => $stuId
        ));
        $row = $pstm->columnCount();

        if ($row > 0) {
            $result = $pstm->fetch(PDO::FETCH_ASSOC);
            return $result['Reg_ID'];
        } else {
            return null;
        }
    }

    public function getStudentFromRegId($regId)
    {
        $sql = "SELECT * FROM TABLE9,TABLE13
                WHERE TABLE9.St_ID = TABLE13.stu_id
                AND TABLE9.Reg_ID = :regId";
        $pstm = $this->connect->prepare($sql);
        $pstm->execute(array(
            ':regId' => $regId
        ));
        $result = $pstm->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getRegisterDetail($regId)
    {
        $sql = "SELECT * FROM TABLE12,TABLE9
                WHERE TABLE12.`Reg-ID` = TABLE9.Reg_ID
                AND `Reg-ID` = :regId ;";
        $pstm = $this->connect->prepare($sql);
        $pstm->execute(array(
            ':regId' => $regId
        ));
        return $pstm->fetchAll();
    }

    public function getRegisterByRegId($regId)
    {
        $sql = "SELECT * FROM TABLE9,TABLE16
                WHERE TABLE9.coupon_id = TABLE16.coupon_id
                AND Reg_ID = :regId;";
        $pstm = $this->connect->prepare($sql);
        $pstm->execute(array(
            'regId' => $regId
        ));
        return $pstm->fetch(PDO::FETCH_ASSOC);
    }

    public function cancelRegister($regId)
    {
        $sql = "UPDATE TABLE9 SET Reg_status = 'ยกเลิก' WHERE TABLE9.Reg_ID = :regId ;";
        $pstm = $this->connect->prepare($sql);
        $result = $pstm->execute(array(
            ':regId' => $regId
        ));
        return $result;
    }

    public function registerDetail($regId)
    {
        $sql = "SELECT * FROM TABLE9
                LEFT JOIN TABLE12 ON TABLE9.Reg_ID = TABLE12.`Reg-ID`
                WHERE TABLE9.Reg_ID = :regId ;";
        $pstm = $this->connect->prepare($sql);
        $pstm->execute(array(
            ':regId' => $regId
        ));

        return $pstm->fetchAll();
    }

    public function updateRegister()
    {
        $regId = filter_input(INPUT_POST, 'regId', FILTER_SANITIZE_STRING);
        $braId = filter_input(INPUT_POST, 'bra_id', FILTER_SANITIZE_STRING);
        $note = filter_input(INPUT_POST, 'coupon_number', FILTER_SANITIZE_STRING);
        $empId = filter_input(INPUT_POST, 'emp_id', FILTER_SANITIZE_STRING);

        $sql = "UPDATE TABLE9 SET emp_sale = :empId, `Bra-id` = :braId, `Reg-Note` = :note
                WHERE Reg_ID = :regId";
        $pstm = $this->connect->prepare($sql);
        $pstm->execute(array(
            ':empId' => $empId,
            ':braId' => $braId,
            ':note' => $note,
            ':regId' => $regId
        ));

        return true;
    }

    public function getRegDetAuto($couId, $regId)
    {
        $pstm = $this->connect->prepare("SELECT * FROM TABLE12 WHERE `Reg-ID` = :regId AND `Cou-ID` = :couId ;");
        $pstm->execute(array(
            ':regId' => $regId,
            ':couId' => $couId
        ));

        $result = $pstm->fetch(PDO::FETCH_ASSOC);
        return $result['Reg_Det_Auto'];
    }

    public function getScheduleStudent($stuId){
        $pstm = $this->connect->prepare("SELECT * FROM student_schedule WHERE stu_id = :stuId ");
        $pstm->execute(array(
            ':stuId' => $stuId
        ));
        return $pstm->fetchAll();
    }

    public function RegisterListAllThisYear(){
        $sql = "SELECT `Bra-id`,Reg_ID,Reg_status,`Reg-date`,
                expired_date,stu_prefix,stu_firstname,stu_lastname,
                `Reg-sum`,St_ID FROM TABLE9
                LEFT JOIN TABLE13 ON TABLE9.St_ID = TABLE13.stu_id
                WHERE YEAR(`Reg-date`) BETWEEN (YEAR(NOW())) AND (YEAR(NOW()))
                ORDER BY TABLE9.Reg_ID DESC";
        $pstm = $this->connect->prepare($sql);
        $pstm->execute();
        return $pstm->fetchAll();
    }

    public function RegisterListCompleteThisYear(){
        $sql = "SELECT `Bra-id`,Reg_ID,Reg_status,`Reg-date`,
                expired_date,stu_prefix,stu_firstname,stu_lastname,
                `Reg-sum`,St_ID FROM TABLE9
                LEFT JOIN TABLE13 ON TABLE9.St_ID = TABLE13.stu_id
                WHERE YEAR(`Reg-date`) BETWEEN (YEAR(NOW())) AND (YEAR(NOW()))
                AND Reg_status = 'สมบูรณ์'
                ORDER BY TABLE9.Reg_ID DESC";
        $pstm = $this->connect->prepare($sql);
        $pstm->execute();
        return $pstm->fetchAll();
    }

    public function RegisterListUncompleteThisYear(){
        $sql = "SELECT `Bra-id`,Reg_ID,Reg_status,`Reg-date`,
                expired_date,stu_prefix,stu_firstname,stu_lastname,
                `Reg-sum`,St_ID FROM TABLE9
                LEFT JOIN TABLE13 ON TABLE9.St_ID = TABLE13.stu_id
                WHERE YEAR(`Reg-date`) BETWEEN (YEAR(NOW())) AND (YEAR(NOW()))
                AND Reg_status != 'สมบูรณ์'
                ORDER BY TABLE9.Reg_ID DESC";
        $pstm = $this->connect->prepare($sql);
        $pstm->execute();
        return $pstm->fetchAll();
    }
}
