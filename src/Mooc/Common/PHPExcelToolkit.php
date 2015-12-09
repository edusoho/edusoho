<?php
namespace Mooc\Common;

use PHPExcel_Cell;
use PHPExcel_IOFactory;
use Topxia\Common\FileToolkit;

class PHPExcelToolkit
{
    protected $necessaryFields = array();
    protected $objWorksheet;
    protected $rowTotal    = 0;
    protected $colTotal    = 0;
    protected $excelFields = array();
    protected $fieldSort;

    public function setNecessaryFields($necessaryFields)
    {
        return $this->necessaryFields = array_merge($this->necessaryFields, $necessaryFields);
    }

    public function checkFile($file)
    {
        if (!is_object($file)) {
            $errorMessage = '请选择上传的文件';
            return $errorMessage;
        }

        if (FileToolkit::validateFileExtension($file, 'xls xlsx')) {
            $errorMessage = 'Excel格式不正确！';
            return $errorMessage;
        }
    }

    public function validateExcelFile($file)
    {
        $errorMessage = '';
        $objWorksheet = '';
        $rowAndCol    = '';
        $excelFields  = '';

        list($objWorksheet, $rowAndCol, $excelFields) = $this->excelAnalyse($file);

        if ($this->rowTotal > 1000) {
            $errorMessage = 'Excel超过1000行数据!';
        }

        if (!$this->checkNecessaryFields($this->excelFields)) {
            $errorMessage = '缺少必要的字段';
        }

        return array($objWorksheet, $rowAndCol, $excelFields, $errorMessage);
    }

    public function getFieldSort($excelField, $fieldArray)
    {
        $fieldSort = array();

        foreach ($excelField as $key => $value) {
            $value = $this->trim($value);

            if (in_array($value, $fieldArray)) {
                foreach ($fieldArray as $fieldKey => $fieldValue) {
                    if ($value == $fieldValue) {
                        $fieldSort[] = array("num" => $key, "fieldName" => $fieldKey);
                        break;
                    }
                }
            }
        }

        $this->fieldSort = $fieldSort;
        return $this->fieldSort;
    }

    public function checkRepeatData($checkFields)
    {
        $errorInfo    = array();
        $nicknameData = array();

        foreach ($checkFields as $checkField) {
            foreach ($this->fieldSort as $key => $value) {
                if ($value['fieldName'] == $checkField) {
                    $nickNameCol = $value['num'];
                }
            }

            for ($row = 3; $row <= $this->rowTotal; $row++) {
                $nickNameColData = $this->objWorksheet->getCellByColumnAndRow($nickNameCol, $row)->getValue();

                if ($nickNameColData."" == "") {
                    continue;
                }

                $nicknameData[] = $nickNameColData."";
            }

            $info                            = $this->arrayRepeat($nicknameData);
            empty($info) ? '' : $errorInfo[] = $info;

            return $errorInfo;
        }
    }

    public function getRowData($row)
    {
        $strs     = array();
        $rowData  = array();
        $fieldCol = array();

        for ($col = 0; $col < $this->colTotal; $col++) {
            $infoData   = $this->objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
            $strs[$col] = $infoData."";
            unset($infoData);
        }

        foreach ($this->fieldSort as $sort) {
            $num = $sort['num'];
            $key = $sort['fieldName'];

            $rowData[$key]  = $strs[$num];
            $fieldCol[$key] = $num + 1;
        }

        unset($strs);
        return array($rowData, $fieldCol);
    }

    public function checkEmptyData($rowData, $row)
    {
        $info      = '';
        $emptyData = array_count_values($rowData);

        if (empty($rowData) || (isset($emptyData[""]) && count($rowData) == $emptyData[""])) {
            $info = "第".$row."行为空行，已跳过";
        }

        return $info;
    }

    private function arrayRepeat($array)
    {
        $repeatArray      = array();
        $repeatArrayCount = array_count_values($array);
        $repeatRow        = "";

        foreach ($repeatArrayCount as $key => $value) {
            if ($value > 1) {
                $repeatRow .= "重复:<br>";

                for ($i = 1; $i <= $value; $i++) {
                    $row = array_search($key, $array) + 3;
                    $repeatRow .= "第".$row."行"."    ".$key."<br>";
                    unset($array[$row - 3]);
                }
            }
        }

        return $repeatRow;
    }

    private function excelAnalyse($file)
    {
        $objPHPExcel        = PHPExcel_IOFactory::load($file);
        $objWorksheet       = $objPHPExcel->getActiveSheet();
        $highestRow         = $objWorksheet->getHighestRow();
        $highestColumn      = $objWorksheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelFields        = array();

        for ($col = 0; $col < $highestColumnIndex; $col++) {
            $fieldTitle                                  = $objWorksheet->getCellByColumnAndRow($col, 2)->getValue();
            empty($fieldTitle) ? '' : $excelFields[$col] = $this->trim($fieldTitle);
        }

        $rowAndCol = array('highestRow' => $highestRow, 'highestColumnIndex' => $highestColumnIndex);

        $this->objWorksheet = $objWorksheet;
        $this->rowTotal     = $highestRow;
        $this->colTotal     = $highestColumnIndex;
        $this->excelFields  = $excelFields;

        return array($objWorksheet, $rowAndCol, $excelFields);
    }

    private function trim($data)
    {
        $data = trim($data);
        $data = str_replace(" ", "", $data);
        $data = str_replace('\n', '', $data);
        $data = str_replace('\r', '', $data);
        $data = str_replace('\t', '', $data);

        return $data;
    }

    private function checkNecessaryFields($excelFields)
    {
        $necessaryFields = $this->necessaryFields;

        if (empty($necessaryFields) || $necessaryFields = array_intersect($necessaryFields, array_values($excelFields))) {
            return true;
        }

        return false;
    }
}
