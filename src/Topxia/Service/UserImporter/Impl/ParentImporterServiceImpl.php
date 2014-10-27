<?php
namespace Topxia\Service\UserImporter\Impl;

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Topxia\Service\UserImporter\BaseImporterService;
use Topxia\Service\UserImporter\ParentImporterService;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use Topxia\WebBundle\Twig\Extension\DataDict;

class ParentImporterServiceImpl extends BaseImporterService implements ParentImporterService
{
    private $otherNecessaryFields = array('mobile' => '手机号码', 'childNumber' => '子女学号', 'relation' => '家庭关系');

    public function importUserByUpdate($parents, $classId)
    {
        $this->getUserDao()->getConnection()->beginTransaction();
        try{
            for($i=0;$i<count($parents);$i++){
                $parent = $this->getUserDao()->findUserByMobile($parents[$i]["mobile"]);
                if($parent) {
                    $parent=UserSerialize::unserialize($parent);
                    $this->getUserService()->changePassword($parent["id"],$parents[$i]["password"]);
                    $this->getUserService()->changeEmail($parent["id"],$parents[$i]["email"]);
                    $this->getUserService()->changeTrueName($parent["id"],$parents[$i]["truename"]);
                    $this->getUserService()->updateUserProfile($parent["id"],$parents[$i]); 
                } else {
                    $this->importUserByIgnore($parents, $classId);
                }
                             
            }

            $this->getUserDao()->getConnection()->commit();

        }catch(\Exception $e){
            $this->getUserDao()->getConnection()->rollback();
            throw $e;
        }
    }

    public function importUserByIgnore($parents, $classId)
    {
        $this->getUserDao()->getConnection()->beginTransaction();
        try{

            for($i=0;$i<count($parents);$i++){
                $parent = $this->createUser($parents[$i]);
                $parent['number'] = 'p'.$parents[$i]['mobile'];
                $parent['nickname'] = $parents[$i]['mobile'];
                $parent["roles"]=array('ROLE_USER', 'ROLE_PARENT');
                $parent = UserSerialize::unserialize(
                    $this->getUserDao()->addUser(UserSerialize::serialize($parent))
                );

                $profile = $this->createUserProfile($parent['id'], $parents[$i]);
                $this->getProfileDao()->addProfile($profile);

                $class = $this->getClassesService()->findClassByUserNumber($parents[$i]['childNumber']);
                $child = $this->getUserService()->getUserByNumber($parents[$i]['childNumber']);
                $classMember = array();
                $classMember['userId'] = $parent['id'];
                $classMember['classId'] = $class['id'];
                $classMember['role'] = 'PARENT';
                $classMember['createdTime'] = time();
                $this->getClassesService()->addClassMember($classMember);


                $family = DataDict::dict('family');
                $userRelation = array();
                $userRelation['fromId'] = $parent['id'];
                $userRelation['toId'] = $child['id'];
                $userRelation['type'] = 'family';
                $userRelation['relation'] = array_search($parents[$i]['relation'], $family);
                $userRelation['createdTime'] = time();
                $this->getUserService()->addUserRelation($userRelation);
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
            $parent = array();
            foreach ($matchFields as $key => $value) {
                if($key == 'truename') {
                    $parent[$key] = $this->trim($rowData[$value - 1]);
                } else {
                   $parent[$key] = $rowData[$value - 1]; 
                }
                
            }
            unset($rowData);
            //姓名和手机号为空直接跳过这行
            if(empty($parent['mobile']) && empty($parent['truename'])) {
                continue;
            }

            if(!$checkEmail) {
                $parent['email'] = $parent['mobile'] . '@' . 'edusoho' . '.' . 'com';
            }

            $errorInfo = $this->validFields($parent, $row, $matchFields, $checkEmail);
            if($errorInfo) {
                $errorInfos = array_merge($errorInfos, $errorInfo);
            }

            $parent['gender'] = $parent['gender'] == '男' ? 'male' : 'female';
            $mobileAarry[$row] = $parent['mobile'];
            $emailAarry[$row] = $parent['email']; 

            if($checkEmail && $rule == "ignore" && !$userService->isEmailAvaliable($parent['email'])) {          
                $errorInfos[] = "第". $row. "行的邮箱已存在，请检查数据．";
                continue;
            }

            if($userService->isNumberAvaliable($parent['childNumber'])) { 
                $errorInfos[] = "第". $row. "行的子女学号不存在．";
                continue;
            }

            if(empty($parent['mobile'])) {
                $errorInfos[] = "第". $row. "行的手机号码不存在．";
                continue;
            }

            if(!$userService->isMobileAvaliable($parent['mobile'])) { 

                if($rule=="ignore") {
                    $checkInfo[]="第" . $row. "行的手机号码已存在，已略过"; 
                    continue;
                }
                if($rule=="update") {
                    $checkInfo[]="第" . $row . "行的手机号码已存在，将会更新";          
                }
                $allStuentData[]= $parent;            
                continue;
            }

            $allStuentData[]= $parent;
            unset($parent);
        }

        $mobileRepeatInfo = $this->arrayRepeat($mobileAarry, "手机号码");
        if($checkEmail) {
            $emailRepeatInfo = $this->arrayRepeat($emailAarry, "邮箱");
        }
        
        $errorInfos = array_merge($errorInfos, $mobileRepeatInfo, $emailRepeatInfo);
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