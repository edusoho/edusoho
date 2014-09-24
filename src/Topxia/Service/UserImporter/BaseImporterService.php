<?php
namespace Topxia\Service\UserImporter;

use Topxia\Service\Common\BaseService;
use Topxia\Common\SimpleValidator;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Topxia\WebBundle\Twig\Extension\DataDict;

abstract class BaseImporterService extends BaseService
{
	protected $necessaryFields = array('password' => '密码', 'truename' => '姓名', 'gender' => '性别');

	protected function importUserByIgnore($users, $classId)
    {

    }

    // importUserWithUpdateRule ?
    protected function importUserByUpdate($users, $classId)
    {

    }

    protected function checkUserData($file, $rule, $classId)
    {
        
    }

	protected function checkNecessaryFields($excelTitle, $otherFields = array())
	{
		$errorInfo = array();
		$checkFields = array_merge($this->necessaryFields, $otherFields);
		foreach ($checkFields as $key => $value) {
			if(!in_array($value, $excelTitle)) {
				$errorInfo[] = $value; 
			}
		}
		return $errorInfo;
	}

    protected function createUserProfile($id, $user)
    {
        $profile = array();
        $profile['id'] = $id;
        $profile['mobile'] = empty($user['mobile']) ? '' : $user['mobile'];
        $profile['idcard'] = empty($user['idcard']) ? '' : $user['idcard'];
        $profile['company'] = empty($user['company']) ? '' : $user['company'];
        $profile['job'] = empty($user['job']) ? '' : $user['job'];
        $profile['weixin'] = empty($user['weixin']) ? '' : $user['weixin'];
        $profile['weibo'] = empty($user['weibo']) ? '' : $user['weibo'];
        $profile['qq'] = empty($user['qq']) ? '' : $user['qq'];
        $profile['site'] = empty($user['site']) ? '' : $user['site'];
        $profile['gender'] = empty($user['gender']) ? 'secret' : $user['gender'];
        for($j=1;$j<=5;$j++){
            $profile['intField'.$j] = empty($user['intField'.$j]) ? null : $user['intField'.$j];
            $profile['dateField'.$j] = empty($user['dateField'.$j]) ? null : $user['dateField'.$j];
            $profile['floatField'.$j] = empty($user['floatField'.$j]) ? null : $user['floatField'.$j];
        }
        for($j=1;$j<=10;$j++){
            $profile['varcharField'.$j] = empty($user['varcharField'.$j]) ? "" : $user['varcharField'.$j];
            $profile['textField'.$j] = empty($user['textField'.$j]) ? "" : $user['textField'.$j];
        }

        return $profile;
    }

    protected function createUser($data)
    {
        $user = array();
        $user['email'] = $data['email'];
        $user['truename'] = $data['truename'];
        $user['type'] = "default";
        $user['createdIp'] = "";
        $user['createdTime'] = time();
        $user['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $user['password'] = $this->getPasswordEncoder()->encodePassword($data['password'], $user['salt']);
        $user['setup'] = 1;

        return $user;
    }

	protected function validFields($userData,$row,$fieldCol,$checkEmail)
	{    
	    $errorInfo=array();

        if($checkEmail) {
            if (!SimpleValidator::email($userData['email'])) {
                $errorInfo[]="第 ".$row."行".$fieldCol["email"]." 列 的数据存在问题，请检查。";
            }
        }

        if (isset($userData['number']) && !SimpleValidator::number($userData['number'])) {
            $errorInfo[]="第 ".$row."行".$fieldCol["number"]." 列 的数据存在问题，请检查。";
        }

        if (!SimpleValidator::password($userData['password'])) {
            $errorInfo[]="第 ".$row."行".$fieldCol["password"]." 列 的数据存在问题，请检查。";
        }

        if (isset($userData['childNumber']) && !SimpleValidator::number($userData['childNumber'])) {
            $errorInfo[]="第 ".$row."行".$fieldCol["childNumber"]." 列 的数据存在问题，请检查。";
        }

        if (isset($userData['truename'])&&$userData['truename']!=""&& !SimpleValidator::truename($userData['truename'])) {
            $errorInfo[]="第 ".$row."行".$fieldCol["truename"]." 列 的数据存在问题，请检查。";
        }

        if (isset($userData['idcard']) &&$userData['idcard']!=""&& !SimpleValidator::idcard($userData['idcard'])) {
            $errorInfo[]="第 ".$row."行".$fieldCol["idcard"]." 列 的数据存在问题，请检查。";
        }

        if (isset($userData['mobile'])&&$userData['mobile']!=""&& !SimpleValidator::mobile($userData['mobile'])) {
            $errorInfo[]="第 ".$row."行".$fieldCol["mobile"]." 列 的数据存在问题，请检查。";
        }

        if (isset($userData['gender']) && !in_array($userData['gender'], array("男","女"))){
            $errorInfo[]="第 ".$row."行".$fieldCol["gender"]." 列 的数据存在问题，请检查。";
        }
        
        if (isset($userData['relation']) && !in_array($userData['relation'], array_values(DataDict::dict('family')))){
            $errorInfo[]="第 ".$row."行".$fieldCol["gender"]." 列 的数据存在问题，请检查。";
        }

        if (isset($userData['qq'])&&$userData['qq']!=""&& !SimpleValidator::qq($userData['qq'])){
            $errorInfo[]="第 ".$row."行".$fieldCol["qq"]." 列 的数据存在问题，请检查。";
        }

        if (isset($userData['site'])&&$userData['site']!=""&& !SimpleValidator::site($userData['site'])){
            $errorInfo[]="第 ".$row."行".$fieldCol["site"]." 列 的数据存在问题，请检查。";
        }

        if (isset($userData['weibo'])&&$userData['weibo']!=""&& !SimpleValidator::site($userData['weibo'])){
            $errorInfo[]="第 ".$row."行".$fieldCol["weibo"]." 列 的数据存在问题，请检查。";
        }

        for($i=1;$i<=5;$i++){
            if (isset($userData['intField'.$i])&&$userData['intField'.$i]!=""&& !SimpleValidator::integer($userData['intField'.$i])){
            $errorInfo[]="第 ".$row."行".$fieldCol["intField".$i]." 列 的数据存在问题，请检查(必须为整数,最大到9位整数)。";
             }
            if (isset($userData['floatField'.$i])&&$userData['floatField'.$i]!=""&& !SimpleValidator::float($userData['floatField'.$i])){
            $errorInfo[]="第 ".$row."行".$fieldCol["floatField".$i]." 列 的数据存在问题，请检查(只保留到两位小数)。";
             }
            if (isset($userData['dateField'.$i])&&$userData['dateField'.$i]!=""&& !SimpleValidator::date($userData['dateField'.$i])){
            $errorInfo[]="第 ".$row."行".$fieldCol["dateField".$i]." 列 的数据存在问题，请检查(格式如XXXX-MM-DD)。";
             }
        }

        return $errorInfo;
	}

	protected function getFieldArray($otherNecessaryFields)
	{
		$userFieldArray=array();

        $userFields=$this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();
        $fieldArray=array_merge(array(
                "email"=>'邮箱',
                "password"=>'密码',
                "truename"=>'姓名',
                "gender"=>'性别',
                "idcard"=>'身份证号',
                "mobile"=>'手机号码',
                "company"=>'公司',
                "job"=>'职业',
                "site"=>'个人主页',
                "weibo"=>'微博',
                "weixin"=>'微信',
                "qq"=>'QQ',
                ), $otherNecessaryFields);
        
        foreach ($userFields as $userField) {
            $title=$userField['title'];

            $userFieldArray[$userField['fieldName']]=$title;
        }
        $fieldArray=array_merge($fieldArray,$userFieldArray);
        return $fieldArray;
	}

	protected function matchExcelTitle($excelTitle, $fieldArray)
	{
		$matchResult = array();
		foreach ($excelTitle as $key => $value) {
			$index = array_search($value, $fieldArray);
			if($index) {
				$matchResult[$index] = $key + 1;
			}
		}
		return $matchResult;
	}

	protected function trim($data)
	{       
	    $data=trim($data);
	    $data=str_replace(" ","",$data);
	    $date=str_replace("　","",$data);
	    $data=str_replace('\n','',$data);
	    $data=str_replace('\r','',$data);
	    $data=str_replace('\t','',$data);

	    return $data;
	}

   	protected function arrayRepeat($array, $type)
    {   
        $repeatArray = array();
        $repeatArrayCount = array_count_values($array);
        $repeatError = array();

        foreach ($repeatArrayCount as $key => $value) {
            if($value > 1) {
            	$repeatRow = "";
	        	for($i=1;$i<=$value;$i++){
		            $row=array_search($key, $array);
		            if($i < $value ) {
		            	$repeatRow .= "第" . $row . "行" . $type . $key . "与";
		            } else {
		            	$repeatRow .= "第" . $row . "行" . $type . $key . "重复";
		            }
		            unset($array[$row]);
	            }
               $repeatError[] = $repeatRow;
            }
        }
        return $repeatError;
    }  

	protected function getUserFieldService()
    {
        return $this->createService('User.UserFieldService');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getClassesService()
    {
        return $this->createService('Classes.ClassesService');
    }

    protected function getUserDao()
    {
        return $this->createDao('User.UserDao');
    }

    protected function getProfileDao()
    {
        return $this->createDao('User.UserProfileDao');
    }

    protected function getPasswordEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }
}
