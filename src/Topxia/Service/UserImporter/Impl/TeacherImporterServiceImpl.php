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
                    $this->getUserService()->changeNickname($teacher["id"],$teachers[$i]["truename"]);
                    $this->getUserService()->changeEmail($teacher["id"],$teachers[$i]["email"]);
                    $this->getUserService()->changeTrueName($teacher["id"],$teachers[$i]["truename"]);
                    $this->getUserService()->updateUserProfile($teacher["id"],$teachers[$i]); 
                } else {
                    $this->importTeacherByIgnore($teachers, $classId);
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
                $teacher = array();
                $teacher['email'] = $teachers[$i]['email'];
                $teacher['number'] = $teachers[$i]['number'];
                $teacher['truename'] = $teachers[$i]['truename'];
                $teacher['nickname'] = $teachers[$i]['truename'];
                $teacher["roles"]=array('ROLE_TEACHER');
                $teacher['type'] = "default";
                $teacher['createdIp'] = "";
                $teacher['createdTime'] = time();
                $teacher['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
                $teacher['password'] = $this->getPasswordEncoder()->encodePassword($teachers[$i]['password'], $teacher['salt']);
                $teacher['setup'] = 1;

                $teacher = UserSerialize::unserialize(
                    $this->getUserDao()->addUser(UserSerialize::serialize($teacher))
                );

                $profile = array();
                $profile['id'] = $teacher['id'];
                $profile['mobile'] = empty($teachers[$i]['mobile']) ? '' : $teachers[$i]['mobile'];
                $profile['idcard'] = empty($teachers[$i]['idcard']) ? '' : $teachers[$i]['idcard'];
                $profile['company'] = empty($teachers[$i]['company']) ? '' : $teachers[$i]['company'];
                $profile['job'] = empty($teachers[$i]['job']) ? '' : $teachers[$i]['job'];
                $profile['weixin'] = empty($teachers[$i]['weixin']) ? '' : $teachers[$i]['weixin'];
                $profile['weibo'] = empty($teachers[$i]['weibo']) ? '' : $teachers[$i]['weibo'];
                $profile['qq'] = empty($teachers[$i]['qq']) ? '' : $teachers[$i]['qq'];
                $profile['site'] = empty($teachers[$i]['site']) ? '' : $teachers[$i]['site'];
                $profile['gender'] = empty($teachers[$i]['gender']) ? 'secret' : $teachers[$i]['gender'];
                for($j=1;$j<=5;$j++){
                    $profile['intField'.$j] = empty($teachers[$i]['intField'.$j]) ? null : $teachers[$i]['intField'.$j];
                    $profile['dateField'.$j] = empty($teachers[$i]['dateField'.$j]) ? null : $teachers[$i]['dateField'.$j];
                    $profile['floatField'.$j] = empty($teachers[$i]['floatField'.$j]) ? null : $teachers[$i]['floatField'.$j];
                }
                for($j=1;$j<=10;$j++){
                    $profile['varcharField'.$j] = empty($teachers[$i]['varcharField'.$j]) ? "" : $teachers[$i]['varcharField'.$j];
                    $profile['textField'.$j] = empty($teachers[$i]['textField'.$j]) ? "" : $teachers[$i]['textField'.$j];
                }

                $this->getProfileDao()->addProfile($profile);
            
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
            $teacher = array();
            foreach ($matchFields as $key => $value) {
                if($key == 'truename') {
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
            $numberAarry[$row] = $teacher['number'];
            $emailAarry[$row] = $teacher['email']; 
            
            if($checkEmail && !$userService->isEmailAvaliable($teacher['email']))
            {     
                $errorInfos[] = "第".$row."行的邮箱已存在，请检查数据．";
                continue;
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

   
        $numberRepeatInfo = $this->arrayRepeat($numberAarry, "工号");
        if($checkEmail) {
            $emailRepeatInfo = $this->arrayRepeat($emailAarry, "邮箱");
        }

        $errorInfos = array_merge($checkInfo, $numberRepeatInfo, $emailRepeatInfo);
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