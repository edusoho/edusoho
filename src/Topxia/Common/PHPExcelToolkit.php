<?php
namespace Topxia\Common;

use PHPExcel;
use PHPExcel_IOFactory;

class PHPExcelToolkit
{
    public static function export($data, $info)
    {
        $title = array(
            'nickname' => '昵称',
            'truename' => '真实姓名',
            'mobile' => '手机号码',
        );
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        // Set document properties
        $objPHPExcel->getProperties()
            ->setCreator($info['creator'])
            ->setLastModifiedBy($info['creator'])
            ->setTitle("Office 2007 XLSX Document")
            ->setSubject("Office 2007 XLSX Document")
            ->setDescription("Document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Export file");
        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A1", $title['nickname'])
            ->setCellValue("B1", $title['truename'])
            ->setCellValue("C1", $title['mobile']);
        if (!empty($data)) {
            $index = 2;
            foreach ($data as $one) {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue("A{$index}", $one['nickname'])
                    ->setCellValue("B{$index}", $one['truename'])
                    ->setCellValue("C{$index}", $one['mobile']);
                $index++;
            }
        }
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle($info['sheetName']);
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        return $objWriter;
    }
}
