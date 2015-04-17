<?php
namespace Topxia\Service\Importer;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\FileToolkit;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use Exception;

class ClassroomUserImporterProcessor implements ImporterProcessor
{
	protected $necessaryFields = array('nickname' => '用户名');

	public function validateExcelFile($file)
	{
		$errorMessage = '';

        if(!is_object($file)){
            $errorMessage = '请选择上传的文件';
            return $errorMessage;
        }

        if (FileToolkit::validateFileExtension($file,'xls xlsx')) {
            $errorMessage = 'Excel格式不正确！';
            return $errorMessage;
        }

        list($objWorksheet,$rowAndCol,$excelField) = $this->excelAnalyse($file);

        if ($rowAndCol['rowLength'] > 1000) {
            $message = 'Excel超过1000行数据!';
            return $errorMessage;
        } 

        if (!$this->checkNecessaryFields($excelField)) {
            $message = '缺少必要的字段';
            return $errorMessage;
        }

        return $errorMessage;
	}

    public function excelAnalyse($file)
    {
    	$objPHPExcel = PHPExcel_IOFactory::load($file);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow(); 
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);   
        $excelFields = array();

        for ($col = 0;$col < $highestColumnIndex;$col++)
        {
            $fieldTitle = $objWorksheet->getCellByColumnAndRow($col, 2)->getValue();
            empty($fieldTitle) ? '' : $excelFields[$col] = $this->trim($fieldTitle);
        }   

        $rowAndCol = array('rowLength' => $highestRow,'colLength' => $highestColumnIndex);

        return array($objWorksheet,$rowAndCol,$excelFields);
    }

    public function getRowTotal($objWorksheet)
    {
    	return $objWorksheet->getHighestRow();
    }

    public function getColTotal($objWorksheet)
    {
    	$highestColumn = $objWorksheet->getHighestColumn();
        return PHPExcel_Cell::columnIndexFromString($highestColumn);
    }

    public function getExcelFieldsValue($objWorksheet, $colTotal, $row)
    {
    	$columnsData = array();
    	for ($col = 0; $col < $colTotal; $col++)
        {
            $infoData = $objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
            $columnsData[$col] = $infoData."";
        }

        return $columnsData;
    }

    public function validExcelFieldValue($userData,$row,$fieldCol)
    {
    	$errorInfo = '';

        if (!$this->getUserService()->getUserByNickname($userData['nickname'])) {
            $errorInfo = "第 ".$row."行".$fieldCol["nickname"]." 列 的用户数据不存在，请检查。";
        }

        return $errorInfo;
    }

	public function checkRepeatData($fieldSort, $highestRow, $objWorksheet)
	{
		$errorInfo=array();
        $nicknameData=array();

        foreach ($fieldSort as $key => $value) {
            if ($value['fieldName'] == 'nickname' ){
                $nickNameCol = $value['num'];
            }
        }

        for ($row=3; $row <= $highestRow; $row++) {

            $nickNameColData = $objWorksheet->getCellByColumnAndRow($nickNameCol, $row)->getValue();      
            if ($nickNameColData."" == "") continue;
            $nicknameData[] = $nickNameColData.""; 
        }

        $info = $this->arrayRepeat($nicknameData);
        empty($info) ? '' : $errorInfo[] = $info;

        return $errorInfo;
	}

	public function arrayRepeat($array)
	{
		$repeatArray = array();
        $repeatArrayCount = array_count_values($array);
        $repeatRow = "";

        foreach ($repeatArrayCount as $key => $value) {
            if ($value > 1) {
                $repeatRow.="重复:<br>";

                for ($i=1; $i<=$value; $i++) {
                    $row = array_search($key, $array)+3;
                    $repeatRow .= "第".$row."行"."    ".$key."<br>";
                    unset($array[$row-3]);
                }
            }
        }

        return $repeatRow;
	}
	
	public function getFieldSort($excelFields)
	{
		$fieldSort = array();
		$necessaryFields = $this->necessaryFields;
        
     	foreach($excelFields as $key => $value){
     		if(in_array($value, $necessaryFields)){
                foreach ($necessaryFields as $fieldKey => $fieldValue) {
                    if ($value == $fieldValue) {
                         $fieldSort[$fieldKey] = array("num"=>$key,"fieldName"=>$fieldKey);
                         break;
                    }
                }
        	}
     	}

         return $fieldSort;
	}

	public function checkNecessaryFields($excelFields)
	{
		$necessaryFields = $this->necessaryFields;
		if ($necessaryFields = array_intersect($necessaryFields, array_values($excelFields))) {
			return true;
		}

		return false;
	}

	public function getUserData($objWorksheet, $rowAndCol, $fieldSort)
	{
		$userCount = 0;

		for ($row = 3;$row <= $rowAndCol['rowLength'];$row++) 
        {
            for ($col = 0; $col < $rowAndCol['colLength']; $col++)
            {
                $infoData = $objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
                $columnsData[$col] = $infoData."";
            }

            foreach ($fieldSort as $sort) {
                $userData[$sort['fieldName']] = $columnsData[$sort['num']];
                $fieldCol[$sort['fieldName']] = $sort['num']+1;
            }

            $emptyData = array_count_values($userData);
            if (isset($emptyData[""]) && count($userData) == $emptyData[""]) {
                $checkInfo[] = "第".$row."行为空行，已跳过";
                continue;
            }

            $info = $this->validExcelFieldValue($userData,$row,$fieldCol);
            empty($info) ? '' : $errorInfo[] = $info;

            $userCount = $userCount+1; 
            $allUserData[] = $userData;
            unset($userData);
        }

        $allUserData=json_encode($allUserData);

        $data['errorInfo'] = empty($errorInfo) ? array() : $errorInfo;
        $data['checkInfo'] = empty($checkInfo) ? array() : $checkInfo;
        $data['userCount'] = $userCount;
        $data['allUserData'] = empty($allUserData) ? array() : $allUserData;

        return $data;
	}

	private function trim($data)
    {       
        $data=trim($data);
        $data=str_replace(" ","",$data);
        $data=str_replace('\n','',$data);
        $data=str_replace('\r','',$data);
        $data=str_replace('\t','',$data);

        return $data;
    }

	protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    
}