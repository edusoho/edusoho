<?php
namespace Topxia\Service\UserImporter\Impl;

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Topxia\Service\UserImporter\BaseImporterService;
use Topxia\Service\UserImporter\TeacherImporterService;
use PHPExcel_IOFactory;
use PHPExcel_Cell;

class TeacherImporterServiceImpl extends BaseImporterService implements TeacherImporterService
{
    private $otherNecessaryFields = array('number' => '工号');

    public function importUserByUpdate($teachers, $classId)
    {
        $this->getUserDao()->getConnection()->beginTransaction();
        try{
            for($i=0;$i<count($teachers);$i++){
                $teacher = $this->getUserDao()->getUserByNumber($teachers[$i]["number"]);
                if($teacher) {
                    $teacher=UserSerialize::unserialize($teacher);
                    $this->getUserService()->changePassword($teacher["id"],$teachers[$i]["password"]);
                    $this->getUserService()->changeEmail($teacher["id"],$teachers[$i]["email"]);
                    $this->getUserService()->changeTrueName($teacher["id"],$teachers[$i]["truename"]);
                    empty($teachers[$i]['mobile']) ? : $this->getUserService()->changeMobile($teacher["id"],$teachers[$i]['mobile']);
                    $this->getUserService()->updateUserProfile($teacher["id"],$teachers[$i]); 
                } else {
                    $this->importUserByIgnore($teachers, $classId);
                }
                             
            }

            $this->getUserDao()->getConnection()->commit();

        }catch(\Exception $e){
            $this->getUserDao()->getConnection()->rollback();
            throw $e;
        }
    }

    public function importUserByIgnore($teachers, $classId)
    {
        $this->getUserDao()->getConnection()->beginTransaction();
        try{

            for($i=0;$i<count($teachers);$i++){
                $teacher = $this->createUser($teachers[$i]);
                $teacher['number'] = $teachers[$i]['number'];
                $teacher['nickname'] = $teachers[$i]['number'];
                $teacher["roles"]=array('ROLE_USER','ROLE_TEACHER');
                $teacher = UserSerialize::unserialize(
                    $this->getUserDao()->addUser(UserSerialize::serialize($teacher))
                );

                $profile = $this->createUserProfile($teacher['id'], $teachers[$i]);
                $this->getProfileDao()->addProfile($profile);
            }

             $this->getUserDao()->getConnection()->commit();

        }catch(\Exception $e){
            $this->getUserDao()->getConnection()->rollback();
            throw $e;
        }
    }

    // @todo refactor.
    public function checkUserData($file, $rule, $classId)
    {
        $result = array();
        $errorInfos = array();
        $checkInfo = array();
        $numberArray = array();
        $emailArray = array();
        $mobileArray = array();
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
            if($title == '手机号码') {
                $checkMobile = true;
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
            $teacher = array();
            foreach ($matchFields as $key => $value) {
                if($key == 'truename' || $key == 'number') {
                    $teacher[$key] = $this->trim($rowData[$value - 1]);
                } else {
                   $teacher[$key] = $rowData[$value - 1]; 
                }
                
            }
            unset($rowData);
            //姓名和学号为空直接跳过这行
            if(empty($teacher['number']) && empty($teacher['truename'])) {
                continue;
            }

            if(!$checkEmail) {
                $teacher['email'] = $teacher['number'] . '@' . 'edusoho' . '.' . 'com';
            }

            $errorInfo = $this->validFields($teacher, $row, $matchFields, $checkEmail);
            
            if($errorInfo) {
                $errorInfos = array_merge($errorInfos, $errorInfo);
            }

            $teacher['gender'] = $teacher['gender'] == '男' ? 'male' : 'female';
            $numberArray[$row] = $teacher['number'];
            $emailArray[$row] = $teacher['email']; 
            if($teacher['mobile'])  {
                $mobileArray[$row] = $teacher['mobile']; 
            }
            
            if($checkEmail && $rule=="ignore" && !$userService->isEmailAvaliable($teacher['email']))
            {     
                $errorInfos[] = "第".$row."行的邮箱已存在，请检查数据．";
                continue;
            }

            $existTeacher = $userService->getUserByNumber($teacher['number']);
            $existMobile = $existTeacher ? $existTeacher['mobile'] : null;
            if($teacher['mobile'] && $existMobile != $teacher['mobile'] && !$userService->isMobileAvaliable($teacher['mobile'])) {
                $errorInfos[] = '第' . $row . '行手机号码为' . $teacher['mobile'] . '已存在，请检查数据．';
            }

            if(!$userService->isNumberAvaliable($teacher['number'])) { 

                if($rule=="ignore") {
                    $checkInfo[]="第".$row."行的工号已存在，已略过"; 
                    continue;
                }
                if($rule=="update") {
                    $checkInfo[]="第".$row."行的工号已存在，将会更新";          
                }
                $allStuentData[]= $teacher;            
                continue;
            }
           
            $allStuentData[]= $teacher;
            unset($teacher);
        }

   
        $numberRepeatInfo = $this->arrayRepeat($numberArray, "工号");
        if($checkEmail) {
            $emailRepeatInfo = $this->arrayRepeat($emailArray, "邮箱");
        }
        if($checkMobile) {
            $mobileRepeatInfo = $this->arrayRepeat($mobileArray, '手机号码');
        }
        $errorInfos = array_merge($errorInfos, $numberRepeatInfo, $emailRepeatInfo, $mobileRepeatInfo);
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