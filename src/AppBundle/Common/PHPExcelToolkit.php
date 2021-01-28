<?php

namespace AppBundle\Common;

use PHPExcel;
use PHPExcel_IOFactory;

class PHPExcelToolkit
{
    //TO DO 异常处理
    public static function export($data, $info)
    {
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        // Set document properties
        $objPHPExcel->getProperties()
            ->setCreator($info['creator'])
            ->setLastModifiedBy($info['creator'])
            ->setTitle('Office 2007 XLSX Document')
            ->setSubject('Office 2007 XLSX Document')
            ->setDescription('Document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Export file');
        $activieSheet = $objPHPExcel->setActiveSheetIndex(0);
        $index = 0;
        foreach ($info['title'] as $key => $value) {
            $char = chr(65 + $index);
            ++$index;
            $activieSheet->setCellValue("{$char}1", $value);
            $activieSheet->getColumnDimension($char)->setWidth(14);
        }

        $activieSheet->getRowDimension('1')->setRowHeight(18);
        if (!empty($data)) {
            $index = 2;
            foreach ($data as $one) {
                $i = 0;
                foreach ($info['title'] as $key => $value) {
                    $cellValue = $one[$key];
                    if ($key == 'createdTime') {
                        $cellValue = date('Y-m-d', $cellValue);
                    }
                    $char = chr(65 + $i);
                    ++$i;
                    $activieSheet->setCellValue("{$char}{$index}", $cellValue);
                }
                $activieSheet->getRowDimension($index)->setRowHeight(18);
                ++$index;
            }
        }
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle($info['sheetName']);
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        return $objWriter;
    }
}
