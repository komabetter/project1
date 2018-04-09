<?php
namespace core;

use PHPMailer;
require ("PHPmailer/class.phpmailer.php");

class Mail
{

    private $mail;

    private $date;

    public function __construct()
    {
        $this->mail = new PHPMailer();
        $this->mail->IsSMTP();
        $this->mail->SMTPDebug = 0;
        $this->mail->SMTPAuth = true;
        $this->mail->SMTPSecure = 'ssl';
        $this->mail->Host = "smtp.gmail.com";
        // on server use 465 local use 45
        $this->mail->Port = 465;
        $this->mail->Username = "email";
        $this->mail->Password = "password";
        $this->mail->CharSet = "UTF-8";
        $this->mail->FromName = "Nes Education";
        $this->mail->From = "email";
        $this->mail->Subject = "Nes Education";
        $this->mail->IsHTML(true);
        $this->date = date('F-d-Y');
    }

    public function sendMailFormA($stu, $regId, $recId, $tragetEmail, $studentVisa)
    {
        $stuName = $stu['stu_firstname'] . " " . $stu['stu_lastname'];
        $stuId = $stu['stu_id'];
        $stuMail = $stu['stu_email'];
        $stuTel = $stu['stu_tel'];
        $stuMobile = $stu['stu_mobile'];
        $stuReceicenNew = $stu['receivenews'];

        $stuPassport = "";
        $citizenInPassport = "___-___";
        $edVisaPacage = "";

        if (count($studentVisa) > 0) {

            $stuPassport = $studentVisa['passport_number'];
            $citizenInPassport = $studentVisa['address_country_name'];
            $edVisaPacage = $studentVisa['extension_name'];
        }

        if ($stuReceicenNew == "Y_th") {
            $message = file_get_contents('PHPmailer/formAThai.php', FILE_USE_INCLUDE_PATH);
        } else {
            $message = file_get_contents('PHPmailer/formA.php', FILE_USE_INCLUDE_PATH);
        }

        $message = preg_replace('/DateTime/', $this->date, $message);
        $message = preg_replace('/stuName/', $stuName, $message);
        $message = preg_replace('/studentId/', $stuId, $message);
        $message = preg_replace('/PassportNumber/', $stuPassport, $message);
        $message = preg_replace('/citizenInPassport/', $citizenInPassport, $message);
        $message = preg_replace('/studentEmail/', $stuMail, $message);
        $message = preg_replace('/studentTel/', $stuTel, $message);
        $message = preg_replace('/studentMobile/', $stuMobile, $message);
        $message = preg_replace('/EDVisaPackage/', $edVisaPacage, $message);

        $this->mail->Body = $message;
        $this->mail->addAttachment('public/pdf/receipt/' . $recId . '.pdf');
        $this->mail->addAttachment('public/pdf/register/' . $regId . '.pdf');
        $this->mail->AddAddress($tragetEmail);

        if (! $this->mail->send()) {
            // echo 'Mailer error: ' . $this->mail->ErrorInfo;
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function sendMailFormB($stuName, $price, $invId, $recId, $tragetEmail, $stuReceicenNew)
    {

        if ($stuReceicenNew == "Y_th") {
            $message = file_get_contents('PHPmailer/formBThai.php', FILE_USE_INCLUDE_PATH);
        } else {
            $message = file_get_contents('PHPmailer/formB.php', FILE_USE_INCLUDE_PATH);
        }

        $message = preg_replace('/DateTime/', $this->date, $message);
        $message = preg_replace('/stuName/', $stuName, $message);
        $price = number_format($price);
        $message = preg_replace('/depositPrice/', $price, $message);

        $this->mail->Body = $message;
        $this->mail->addAttachment('public/pdf/invoice/' . $invId . '.pdf');
        $this->mail->addAttachment('public/pdf/receipt/' . $recId . '.pdf');
        $this->mail->AddAddress($tragetEmail);

        if (! $this->mail->send()) {
            // echo 'Mailer error: ' . $this->mail->ErrorInfo;
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function sendMailFormC()
    {}
}
