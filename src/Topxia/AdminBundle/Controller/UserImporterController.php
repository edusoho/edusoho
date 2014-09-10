<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use Topxia\Common\SimpleValidator;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserImporterController extends BaseController
{   

    public function importUserDataToBaseAction(Request $request)
    {   
        
        $userData=$request->request->get("data");
        $userData=unserialize($userData);
        $checkType=$request->request->get("checkType");
        $userByEmail=array();
        $userByNumber=array();
        $users=array();

        if($checkType=="ignore"){

            $this->getUserImporterService()->importUsers($userData);

        }
        if($checkType=="update"){
            
            foreach ($userData as $key => $user) {
                if ($user["gender"]=="男")$user["gender"]="male";
                if ($user["gender"]=="女")$user["gender"]="female";
                if ($user["gender"]=="")$user["gender"]="secret";

                if($this->getUserService()->getUserByNumber($user["number"])){
                    $userByNumber[]=$user;
                }
                elseif ($this->getUserService()->getUserByEmail($user["email"])){
                    $userByEmail[]=$user;
                }else {
                    $users[]=$user; 
                }      
            }
            $this->getUserImporterService()->importUpdateNumber($userByNumber); 
            $this->getUserImporterService()->importUpdateEmail($userByEmail); 
            $this->getUserImporterService()->importUsers($users);      
        }
        return $this->render('TopxiaAdminBundle:UserImporter:userinfo.excel.step3.html.twig', array(
        ));
    }

    public function importUserInfoByExcelAction(Request $request)
    {
         if ($request->getMethod() == 'POST') {
            $checkType=$request->request->get("rule");
            $file=$request->files->get('excel');
            $errorInfo=array();
            $checkInfo=array();
            $userCount=0;
            $allUserData=array();

            if(!is_object($file)){
                $this->setFlashMessage('danger', '请选择上传的文件');

                return $this->render('TopxiaAdminBundle:UserImporter:userinfo.excel.html.twig', array(
                ));
            }
            if (FileToolkit::validateFileExtension($file,'xls xlsx')) {

                $this->setFlashMessage('danger', 'Excel格式不正确！');

                return $this->render('TopxiaAdminBundle:UserImporter:userinfo.excel.html.twig', array(
                ));
            }

            $objPHPExcel = PHPExcel_IOFactory::load($file);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            $highestRow = $objWorksheet->getHighestRow(); 

            $highestColumn = $objWorksheet->getHighestColumn();
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);   

            if($highestRow>1000){

                $this->setFlashMessage('danger', 'Excel超过1000行数据!');

                return $this->render('TopxiaAdminBundle:UserImporter:userinfo.excel.html.twig', array(
                ));
            }

            $fieldArray=$this->getFieldArray();

            for ($col = 0;$col < $highestColumnIndex;$col++)
            {
                 $fieldTitle=$objWorksheet->getCellByColumnAndRow($col, 2)->getValue();
                 $strs[$col]=$fieldTitle."";
            }   
            $excelField=$strs;
            if(!$this->checkNecessaryFields($excelField,$errorInfo)){

                $this->setFlashMessage('danger', '缺少必要的字段:' . implode(",",$errorInfo));

                return $this->render('TopxiaAdminBundle:UserImporter:userinfo.excel.html.twig', array(
                ));
            }

            $fieldSort=$this->getFieldSort($excelField,$fieldArray);
  
            unset($fieldArray,$excelField);

            $repeatInfo=$this->checkRepeatData($row=3,$fieldSort,$highestRow,$objWorksheet);

            if($repeatInfo){

                $errorInfo[]=$repeatInfo;
                return $this->render('TopxiaAdminBundle:UserImporter:userinfo.excel.step2.html.twig', array(
                    "errorInfo"=>$errorInfo,
                ));

            }

            for ($row = 3;$row <= $highestRow;$row++) 
            {
                $strs=array();

                for ($col = 0;$col < $highestColumnIndex;$col++)
                {
                     $infoData=$objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
                     $strs[$col]=$infoData."";
                     unset($infoData);
                }

                foreach ($fieldSort as $sort) {

                    $num=$sort['num'];
                    $key=$sort['fieldName'];

                    $userData[$key]=$strs[$num];
                    $fieldCol[$key]=$num+1;

                    if($key == 'truename') {
                        $userData[$key] = str_replace(" ", "", $userData[$key]);   
                    }                    
                }
                unset($strs);
                //填充email
                if(!isset($userData['email'])) {
                    $userData['email'] = $userData['number'] . '@' . 'edusoho' . '.' . 'com';
                    $fieldCol['email'] = 'NoRequired';
                }
               
                $emptyData=array_count_values($userData);
                if(isset($emptyData[""])&&count($userData)==$emptyData[""]) {
                    $checkInfo[]="第".$row."行为空行，已跳过";
                    continue;
                }

                if($this->validFields($userData,$row,$fieldCol)){  
                    $errorInfo=array_merge($errorInfo,$this->validFields($userData,$row,$fieldCol));
                    continue;
                }

                if(!$this->getUserService()->isNumberAvaliable($userData['number'])){ 

                    if($checkType=="ignore") {
                        $checkInfo[]="第".$row."行的学号/工号已存在，已略过"; 
                        continue;
                    }
                    if($checkType=="update") {
                        $checkInfo[]="第".$row."行的学号/工号已存在，将会更新";          
                    }
                    $userCount=$userCount+1; 
                    $allUserData[]= $userData;            
                    continue;
                }
                if(!$this->getUserService()->isEmailAvaliable($userData['email'])){          

                    if($checkType=="ignore") {
                        $checkInfo[]="第".$row."行的邮箱已存在，已略过";
                        continue;
                    };
                    if($checkType=="update") {
                        $checkInfo[]="第".$row."行的邮箱已存在，将会更新";
                    }  
                    $userCount=$userCount+1; 
                    $allUserData[]= $userData;     
                    continue;
                }

                $userCount=$userCount+1; 

                $allUserData[]= $userData;
                unset($userData);
            }

            $allUserData=serialize($allUserData);

            return $this->render('TopxiaAdminBundle:UserImporter:userinfo.excel.step2.html.twig', array(
                'userCount'=>$userCount,
                'errorInfo'=>$errorInfo,
                'checkInfo'=>$checkInfo,
                'allUserData'=>$allUserData,
                'checkType'=>$checkType,
            ));

        }

        return $this->render('TopxiaAdminBundle:UserImporter:userinfo.excel.html.twig', array(
        ));
    }

    private function validFields($userData,$row,$fieldCol)
    {    
        $errorInfo=array();

        if(!$fieldCol['email'] === 'NoRequired') {
            if (!SimpleValidator::email($userData['email'])) {
                $errorInfo[]="第 ".$row."行".$fieldCol["email"]." 列 的数据存在问题，请检查。";
            }
        }

        if (!SimpleValidator::number($userData['number'])) {
            $errorInfo[]="第 ".$row."行".$fieldCol["number"]." 列 的数据存在问题，请检查。";
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

    private function checkRepeatData($row,$fieldSort,$highestRow,$objWorksheet)
    {
        $errorInfo=array();
        $emailData=array();
        $numberData=array();

        foreach ($fieldSort as $key => $value) {
            if($value["fieldName"]=="number"){
                $numberCol=$value["num"];
            }
            if($value["fieldName"]=="email"){
                $emailCol=$value["num"];
            }
        }
        
        for ($row=3 ;$row <= $highestRow;$row++) {

            $numberColData=$objWorksheet->getCellByColumnAndRow($numberCol, $row)->getValue();      
            if($numberColData.""=="") continue;
            $numberData[$row]=$numberColData.""; 
        }

        $errorInfo=$this->arrayRepeat($numberData);

        if(isset($emailCol)) {
            for ($row=3 ;$row <= $highestRow;$row++) {

            $emailColData =$objWorksheet->getCellByColumnAndRow($emailCol, $row)->getValue(); 
            if($emailColData.""=="") continue;
            $emailData[$row]=$emailColData."";         
        }

        $errorInfo.=$this->arrayRepeat($emailData);
        }

        return $errorInfo;
    }

    private function arrayRepeat($array)
    {   
        $repeatArray=array();
        $repeatArrayCount=array_count_values($array);
        $repeatRow="";

        foreach ($repeatArrayCount as $key => $value) {
            if($value>1) {$repeatRow.="重复:<br>";
               for($i=1;$i<=$value;$i++){
                $row=array_search($key, $array);
                $repeatRow.="第".$row."行"."    ".$key."<br>";
                unset($array[$row]);
               }
            }
        }

        return $repeatRow;
    }

    private function getFieldSort($excelField,$fieldArray)
    {       
        $fieldSort=array();
        foreach ($excelField as $key => $value) {

            $value=$this->trim($value);

            if(in_array($value, $fieldArray)){
                foreach ($fieldArray as $fieldKey => $fieldValue) {
                    if($value==$fieldValue) {
                         $fieldSort[]=array("num"=>$key,"fieldName"=>$fieldKey);
                         break;
                    }
                }
            }

         }

         return $fieldSort;
    }

    private function checkNecessaryFields($columns,&$errorInfo)
    {   
        if (!in_array('学号/工号', $columns)) {
            $errorInfo[] = '学号/工号';
            return false;
        }
        if (!in_array('密码', $columns)) {
            $errorInfo[] = '密码'; 
            return false;
        }
        if (!in_array('姓名', $columns)) {
            $errorInfo[] = '姓名';
            return false;
        }
        return true;
    }

    private function getFieldArray()
    {       
        $userFieldArray=array();

        $userFields=$this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();
        $fieldArray=array(
                "number"=>'学号/工号',
                "email"=>'邮箱',
                "password"=>'密码',
                "truename"=>'姓名',
                "gender"=>'性别',
                //"idcard"=>'身份证号',
                "mobile"=>'手机号码',
                //"company"=>'公司',
                //"job"=>'职业',
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


    private function trim($data)
    {       
        $data=trim($data);
        $data=str_replace(" ","",$data);
        $data=str_replace('\n','',$data);
        $data=str_replace('\r','',$data);
        $data=str_replace('\t','',$data);

        return $data;
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getUserImporterService()
    {
        return $this->getServiceKernel()->createService('UserImporter.UserImporterService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
