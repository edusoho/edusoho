<?php
namespace UserImporter\Service\UserImporter;

use Topxia\Service\Common\BaseService;

abstract BaseImporterService extends BaseService
{
	protected $necessaryFields = array('password' => '密码', 'truename' => '姓名', 'gender' => '性别');

	protected function loadRawDataFromExcel()
	{
		
	}

	protected function checkNecessaryFields($excelTitle, $otherFields = array())
	{
		$errorInfo = array();
		var $checkFields = array_merge($this->necessaryFields, $otherFields);
		foreach ($checkFields as $key => $value) {
			if(!array_key_exists($key, $excelTitle)) {
				$errorInfo[] = $value . '是必要的字段'; 
			}
		}
		return $errorInfo;
	}

	protected function validFields($userData,$row,$fieldCol)
	{    
	    $errorInfo=array();

	    if (!SimpleValidator::email($userData['email'])) {
	        $errorInfo[]="第 ".$row."行".$fieldCol["email"]." 列 的数据存在问题，请检查。";
	    }

	    if (!SimpleValidator::nickname($userData['nickname'])) {
	        $errorInfo[]="第 ".$row."行".$fieldCol["nickname"]." 列 的数据存在问题，请检查。";
	    }

	    if (!SimpleValidator::password($userData['password'])) {
	        $errorInfo[]="第 ".$row."行".$fieldCol["password"]." 列 的数据存在问题，请检查。";
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
	    if (isset($userData['gender'])&&$userData['gender']!=""&& !in_array($userData['gender'], array("男","女"))){
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

	protected function getFieldArray()
	{
		$userFieldArray=array();

        $userFields=$this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();
        $fieldArray=array(
                "nickname"=>'用户名',
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
                );
        
        foreach ($userFields as $userField) {
            $title=$userField['title'];

            $userFieldArray[$userField['fieldName']]=$title;
        }
        $fieldArray=array_merge($fieldArray,$userFieldArray);
        return $fieldArray;
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

	protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }
}
