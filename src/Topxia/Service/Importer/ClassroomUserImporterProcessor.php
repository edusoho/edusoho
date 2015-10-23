<?php
namespace Topxia\Service\Importer;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\FileToolkit;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use Exception;

class ClassroomUserImporterProcessor implements ImporterProcessor
{
	protected $necessaryFields = array('nickname' => '用户名/邮箱');
	protected $objWorksheet;
	protected $rowTotal = 0;
	protected $colTotal = 0;
	protected $excelFields = array();
	protected $excelExample = 'bundles/classroom/example/classmember_import_example.xls';
	protected $validateRouter = 'classroom_manage_student_import';
	protected $importingRouter = 'classroom_manage_student_to_base';

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

        $this->excelAnalyse($file);

        if ($this->rowTotal > 1000) {
            $message = 'Excel超过1000行数据!';
            return $errorMessage;
        } 

        if (!$this->checkNecessaryFields($this->excelFields)) {
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

        $this->objWorksheet = $objWorksheet;
        $this->rowTotal = $highestRow;
        $this->colTotal = $highestColumnIndex;
        $this->excelFields = $excelFields;

        return array($objWorksheet,$rowAndCol,$excelFields);
    }


    public function getExcelFieldsValue()
    {
    	$columnsData = array();
    	for ($col = 0; $col < $this->colTotal; $col++)
        {
            $infoData = $this->objWorksheet->getCellByColumnAndRow($col, 2)->getFormattedValue();
            $columnsData[$col] = $infoData."";
        }

        return $columnsData;
    }

    public function validExcelFieldValue($userData,$row,$fieldCol)
    {
    	$errorInfo = '';

        if (strpos($userData['nickname'], '@') > 0) {
            $user = $this->getUserService()->getUserByEmail($userData['nickname']);
        } else {
            $user = $this->getUserService()->getUserByNickname($userData['nickname']);
        }

        if (!$user) {
            $errorInfo = "第 ".$row."行".$fieldCol["nickname"]." 列 的用户数据不存在，请检查。";
        }

        return $errorInfo;
    }

	public function checkRepeatData()
	{
		$errorInfo=array();
        $nicknameData=array();
        $fieldSort = $this->getFieldSort();

        foreach ($fieldSort as $key => $value) {
            if ($value['fieldName'] == 'nickname' ){
                $nickNameCol = $value['num'];
            }
        }

        for ($row=3; $row <= $this->rowTotal; $row++) {

            $nickNameColData = $this->objWorksheet->getCellByColumnAndRow($nickNameCol, $row)->getValue();      
            if ($nickNameColData."" == ""){
                continue;
            }
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
	
	public function getFieldSort()
	{
		$fieldSort = array();
		$necessaryFields = $this->necessaryFields;
        $excelFields = $this->excelFields;

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

	public function getUserData()
	{
		$userCount = 0;
		$fieldSort = $this->getFieldSort();

		for ($row = 3;$row <= $this->rowTotal;$row++) 
        {
            for ($col = 0; $col < $this->colTotal; $col++)
            {
                $infoData = $this->objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
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

	public function tryManage($targetId)
	{
		$this->getClassroomService()->tryManageClassroom($targetId);
        return $this->getClassroomService()->getClassroom($targetId);
	}

	public function getExcelExample()
	{
		return $this->excelExample;
	}

	public function getExcelInfoValidateUrl()
	{
		return $this->validateRouter;
	}

	public function getExcelInfoImportUrl()
	{
		return $this->importingRouter;
	}

	public function excelDataImporting($targetObject, $userData, $userUrl)
	{
		$existsUserCount = 0;
        $successCount = 0;

		foreach($userData as $key => $user){
            if (strpos($user['nickname'],'@') > 0) {
                $user = $this->getUserService()->getUserByEmail($user['nickname']);
            } else {
                $user = $this->getUserService()->getUserByNickname($user['nickname']);
            }
            

            $isClassroomStudent = $this->getClassroomService()->isClassroomStudent($targetObject['id'], $user['id']);
            $isClassroomTeacher = $this->getClassroomService()->isClassroomTeacher($targetObject['id'], $user['id']);

            if ($isClassroomStudent || $isClassroomTeacher) {
                $existsUserCount++;
            } else {
                $currentUser = $this->getUserService()->getCurrentUser();

                $order = $this->getOrderService()->createOrder(array(
                    'userId' => $user['id'],
                    'title' => "购买班级《{$targetObject['title']}》(管理员添加)",
                    'targetType' => 'classroom',
                    'targetId' => $targetObject['id'],
                    'amount' => 0,
                    'payment' => 'none',
                    'snPrefix' => 'CR',
                ));

                $this->getOrderService()->payOrder(array(
                    'sn' => $order['sn'],
                    'status' => 'success', 
                    'amount' => 0, 
                    'paidTime' => time(),
                ));

                $info = array(
                    'orderId' => $order['id'],
                    'note'  => '通过批量导入添加',
                );

                if ($this->getClassroomService()->becomeStudent($order['targetId'], $order['userId'], $info)) {
                    $successCount++;
                };

                $member = $this->getClassroomService()->getClassroomMember($targetObject['id'], $user['id']);
                

                $message = array(
                    'classroomId' => $targetObject['id'], 
                    'classroomTitle' => $targetObject['title'],
                    'userId'=> $currentUser['id'],
                    'userName' => $currentUser['nickname'],
                    'type' => 'create');

                $this->getNotificationService()->notify($member['userId'], 'classroom-student', $message);

                $this->getLogService()->info('classroom', 'add_student', "班级《{$targetObject['title']}》(#{$targetObject['id']})，添加学员{$user['nickname']}(#{$user['id']})，备注：通过批量导入添加");
            }
        }

        return array('existsUserCount' => $existsUserCount, 'successCount' => $successCount);
	}

	protected function trim($data)
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

    protected function getClassroomService() 
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getOrderService()
    {
        return ServiceKernel::instance()->createService('Order.OrderService');
    }

    protected function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User.NotificationService');
    }

    protected function getLogService()
    {
        return ServiceKernel::instance()->createService('System.LogService');
    }
}