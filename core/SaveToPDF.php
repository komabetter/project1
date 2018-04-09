<?php
namespace core;

use mPDF;
require 'mpdf/mpdf.php';

class SaveToPDF
{

    public function __construct()
    {}

    public function testpdf()
    {
        $mpdf = new mPDF();
        $mpdf->WriteHTML($html);
    }
}

