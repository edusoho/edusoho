<?php
namespace Topxia\Service\UserImporter\Impl;

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Topxia\Service\UserImporter\BaseImporterService;
use Topxia\Service\UserImporter\StudentImporterService;
use PHPExcel_IOFactory;
use PHPExcel_Cell;

class StudentImporterServiceImpl extends BaseImporterService implements StudentImporterService
{
    private $otherNecessaryFields = array('number' => '学号');

    public function importUserByUpdate($students, $classId)
    {
        $this->getUserDao()->getConnection()->beginTransaction();
        try{
            for($i=0;$i<count($students);$i++){
                $student = $this->getUserDao()->getUserByNumber($students[$i]["number"]);
                if($student) {
                    $student=UserSerialize::unserialize($student);
                    $this->getUserService()->changePassword($student["id"],$students[$i]["password"]);
                    $this->getUserService()->changeEmail($student["id"],$students[$i]["email"]);
                    $this->getUserService()->changeTrueName($student["id"],$students[$i]["truename"]);
                    $this->getUserService()->updateUserProfile($student["id"],$students[$i]); 
                } else {
                    $this->importStudentByIgnore($students, $classId);
                }
                             
            }

            $this->getUserDao()->getConnection()->commit();

        }catch(\Exception $e){
            $this->getUserDao()->getConnection()->rollback();
            throw $e;
        }
    }

    public function importUserByIgnore($students, $classId)
    {
        $this->getUserDao()->getConnection()->beginTransaction();
        try{

            for($i=0;$i<count($students);$i++){
                $student = $this->createUser($students[$i]);
                $student['number'] = $students[$i]['number'];
                $student['nickname'] = $students[$i]['number'];
                $student['roles'] = array('ROLE_USER');
                $student = UserSerialize::unserialize(
                    $this->getUserDao()->addUser(UserSerialize::serialize($student))
                );

                $profile = $this->createUserProfile($student['id'], $students[$i]);
                $this->getProfileDao()->addProfile($profile);

                $classMember = array();
                $classMember['userId'] = $student['id'];
                $classMember['classId'] = $classId;
                $classMember['role'] = 'STUDENT';
                $classMember['createdTime'] = time();
                $this->getClassesService()->addClassMember($classMember);
            }

             $this->getUserDao()->getConnection()->commit();

        }catch(\Exception $e){
            $this->getUserDao()->getConnection()->rollback();
            throw $e;
        }
    }

    public function checkUserData($file, $rule, $classId)
    {
        $result = array();
        $errorInfos = array();
        $checkInfo = array();
        $numberAarry = array();
        $emailAarry = array();
        $allStuentData = array();
        $numberRepeatInfo = "";
        $emailRepeatInfo = "";
        $checkEmail = false;
        $userService = $this->getUserService();
        $classService = $this->getClassesService();

        $objPHPExcel = PHPExcel_IOFactory::load($file);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow(); 

        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);   

        if($highestRow>1000){
            $result['status'] ='failed';
            $result['type'] = 'over_line_limit';
            $result['message'] = 'Excel超过1000行数据!';
            return $result;
        }

        $fieldArray = $this->getFieldArray($this->otherNecessaryFields);
        $execelTitle = array();
        for ($col = 0;$col < $highestColumnIndex;$col++)
        {
            $title = $this->trim($objWorksheet->getCellByColumnAndRow($col, 2)->getValue());
            if($title) {
                $execelTitle[$col] = $title."";
            }
            if($title == '邮箱') {
                $checkEmail = true;
            }
        }

        $errorInfo = $this->checkNecessaryFields($execelTitle,$this->otherNecessaryFields); 
        if($errorInfo) {
            $result['status'] ='failed';
            $result['type'] = 'lack_fields';
            $result['message'] = $errorInfo;
            return $result; 
        }
        $matchFields = $this->matchExcelTitle($execelTitle, $fieldArray);
        for ($row = 3;$row <= $highestRow;$row++) 
        {
            $rowData = array();
            for ($col = 0;$col < $highestColumnIndex;$col++)
            {
                 $colData = $objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
                 $rowData[$col]=$colData."";
                 unset($colData);
            }
            $student = array();
            foreach ($matchFields as $key => $value) {
                if($key == 'truename') {
                    $student[$key] = $this->trim($rowData[$value - 1]);
                } else {
                   $student[$key] = $rowData[$value - 1]; 
                }
                
            }
            unset($rowData);
            //姓名和学号为空直接跳过这行
            if(empty($student['number']) && empty($student['truename'])) {
                continue;
            }

            if(!$checkEmail) {
                $student['email'] = $student['number'] . '@' . 'edusoho' . '.' . 'com';
            }

            $errorInfo = $this->validFields($student, $row, $matchFields, $checkEmail);
            
            if($errorInfo) {
                $errorInfos = array_merge($errorInfos, $errorInfo);
            }

            $student['gender'] = $student['gender'] == '男' ? 'male' : 'female';
            $numberAarry[$row] = $student['number'];
            $emailAarry[$row] = $student['email']; 
            
            if($rule == "ignore" && $classService->findClassMemberByUserNumber($student['number'], $classId)) {
                $errorInfos[] = '学号为' . $student['number'] . '已存在其他班级，请检查';     
                continue;
            }
            
            if($checkEmail && $rule=="ignore" && !$userService->isEmailAvaliable($student['email'])) {          
                $errorInfos[] = "第".$row."行的邮箱已存在，请检查数据．";
                continue;
            }
            if(!$userService->isNumberAvaliable($student['number'])) { 

                if($rule=="ignore") {
                    $checkInfo[]="第".$row."行的学号已存在，已略过"; 
                    continue;
                }
                if($rule=="update") {
                    $checkInfo[]="第".$row."行的学号已存在，将会更新";          
                }
                $allStuentData[]= $student;            
                continue;
            }

            $allStuentData[]= $student;
            unset($student);
        }

   
        $numberRepeatInfo = $this->arrayRepeat($numberAarry, "学号");
        if($checkEmail) {
            $emailRepeatInfo = $this->arrayRepeat($emailAarry, "邮箱");
        }

        $errorInfos = array_merge($errorInfos, $numberRepeatInfo, $emailRepeatInfo);
        $result['status'] ='success';
        $result['errorInfos'] = $errorInfos;
        $result['checkInfo'] = $checkInfo;
        $result['allStuentData'] = $allStuentData;
        return $result;
    }

 

}

class UserSerialize
{
    public static function serialize(array $user)
    {
        $user['roles'] = empty($user['roles']) ? '' :  '|' . implode('|', $user['roles']) . '|';
        return $user;
    }

    public static function unserialize(array $user = null)
    {
        if (empty($user)) {
            return null;
        }
        $user['roles'] = empty($user['roles']) ? array() : explode('|', trim($user['roles'], '|')) ;
        return $user;
    }

    public static function unserializes(array $users)
    {
        return array_map(function($user) {
            return UserSerialize::unserialize($user);
        }, $users);
    }

}