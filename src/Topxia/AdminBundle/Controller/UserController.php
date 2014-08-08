<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\Paginator;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use Topxia\Common\SimpleValidator;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Topxia\Common\ConvertIpToolkit;

class UserController extends BaseController 
{

    public function indexAction (Request $request)
    {
        $fields = $request->query->all();
        $conditions = array(
            'roles'=>'',
            'keywordType'=>'',
            'keyword'=>''
        );

        if(!empty($fields)){
            $conditions =$fields;
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchUserCount($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
      
        return $this->render('TopxiaAdminBundle:User:index.html.twig', array(
            'users' => $users ,
            'paginator' => $paginator
        ));
    }

    public function emailCheckAction(Request $request)
    {
        $email = $request->query->get('value');
        $email = str_replace('!', '.', $email);
        list($result, $message) = $this->getAuthService()->checkEmail($email);
        if ($result == 'success') {
            $response = array('success' => true, 'message' => '该Email地址可以使用');
        } else {
            $response = array('success' => false, 'message' => $message);
        }
        return $this->createJsonResponse($response);
    }

    public function nicknameCheckAction(Request $request)
    {
        $nickname = $request->query->get('value');
        list($result, $message) = $this->getAuthService()->checkUsername($nickname);
        if ($result == 'success') {
            $response = array('success' => true, 'message' => '该昵称可以使用');
        } else {
            $response = array('success' => false, 'message' => $message);
        }
        return $this->createJsonResponse($response);
    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $userData['email'] = $formData['email'];
            $userData['nickname'] = $formData['nickname'];
            $userData['password'] = $formData['password'];
            $userData['createdIp'] = $request->getClientIp();
            $user = $this->getAuthService()->register($userData);
            $this->get('session')->set('registed_email', $user['email']);

            if(isset($formData['roles'])){
                $roles[] = 'ROLE_TEACHER';
                array_push($roles, 'ROLE_USER');
                $this->getUserService()->changeUserRoles($user['id'], $roles);
            }

            $this->getLogService()->info('user', 'add', "管理员添加新用户 {$user['nickname']} ({$user['id']})");

            return $this->redirect($this->generateUrl('admin_user'));
        }
        return $this->render('TopxiaAdminBundle:User:create-modal.html.twig');
    }

    public function editAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        $profile = $this->getUserService()->getUserProfile($user['id']);
        $profile['title'] = $user['title'];

        if ($request->getMethod() == 'POST') {
            $profile = $this->getUserService()->updateUserProfile($user['id'], $request->request->all());

            $this->getLogService()->info('user', 'edit', "管理员编辑用户资料 {$user['nickname']} (#{$user['id']})", $profile);


            return $this->redirect($this->generateUrl('settings'));
        }

        return $this->render('TopxiaAdminBundle:User:edit-modal.html.twig', array(
            'user' => $user,
            'profile'=>$profile
        ));
    }

    public function showAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $profile = $this->getUserService()->getUserProfile($id);
        $profile['title'] = $user['title'];

        $fields=$this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();
        for($i=0;$i<count($fields);$i++){
           if(strstr($fields[$i]['fieldName'], "textField")) $fields[$i]['type']="text";
           if(strstr($fields[$i]['fieldName'], "varcharField")) $fields[$i]['type']="varchar";
           if(strstr($fields[$i]['fieldName'], "intField")) $fields[$i]['type']="int";
           if(strstr($fields[$i]['fieldName'], "floatField")) $fields[$i]['type']="float";
           if(strstr($fields[$i]['fieldName'], "dateField")) $fields[$i]['type']="date";
        }
            
        return $this->render('TopxiaAdminBundle:User:show-modal.html.twig', array(
            'user' => $user,
            'profile' => $profile,
            'fields'=>$fields,
        ));
    }

    public function rolesAction(Request $request, $id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUserService()->getUser($id);

        if ($request->getMethod() == 'POST') {
            $roles = $request->request->get('roles');
            $this->getUserService()->changeUserRoles($user['id'], $roles);

            if (in_array('ROLE_TEACHER', $user['roles']) && !in_array('ROLE_TEACHER', $roles)) {
                $this->getCourseService()->cancelTeacherInAllCourses($user['id']);
            }

            $user = $this->getUserService()->getUser($id);

            return $this->render('TopxiaAdminBundle:User:user-table-tr.html.twig', array(
            'user' => $user
            ));
        }
           
        return $this->render('TopxiaAdminBundle:User:roles-modal.html.twig', array(
            'user' => $user
        ));
    }

    public function avatarAction(Request $request, $id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUserService()->getUser($id);

        $form = $this->createFormBuilder()
            ->add('avatar', 'file')
            ->getForm();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $file = $data['avatar'];

                if (!FileToolkit::isImageFile($file)) {
                    return $this->createMessageResponse('error', '上传图片格式错误，请上传jpg, gif, png格式的文件。');
                }

                $filenamePrefix = "user_{$user['id']}_";
                $hash = substr(md5($filenamePrefix . time()), -8);
                $ext = $file->getClientOriginalExtension();
                $filename = $filenamePrefix . $hash . '.' . $ext;

                $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
                $file = $file->move($directory, $filename);

                $fileName = str_replace('.', '!', $file->getFilename());

                $avatarData = $this->avatar_2($id, $fileName);
                return $this->render('TopxiaAdminBundle:User:user-avatar-crop-modal.html.twig', array(
                    'user' => $user,
                    'filename' => $fileName,
                    'pictureUrl' => $avatarData['pictureUrl'],
                    'naturalSize' => $avatarData['naturalSize'],
                    'scaledSize' => $avatarData['scaledSize']
                ));
            }
        }

        $hasPartnerAuth = $this->getAuthService()->hasPartnerAuth();
        if ($hasPartnerAuth) {
            $partnerAvatar = $this->getAuthService()->getPartnerAvatar($user['id'], 'big');
        } else {
            $partnerAvatar = null;
        }

        return $this->render('TopxiaAdminBundle:User:user-avatar-modal.html.twig', array(
            'form' => $form->createView(),
            'user' => $this->getUserService()->getUser($user['id']),
            'partnerAvatar' => $partnerAvatar,
        ));
    }

    private function avatar_2 ($id, $filename)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $currentUser = $this->getCurrentUser();

        $filename = str_replace('!', '.', $filename);
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);
        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

        try {
            $imagine = new Imagine();
            $image = $imagine->open($pictureFilePath);
        } catch (\Exception $e) {
            @unlink($pictureFilePath);
            return $this->createMessageResponse('error', '该文件为非图片格式文件，请重新上传。');
        }

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(270)->heighten(270);
        $pictureUrl = 'tmp/' . $filename;

        return array(
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
            'pictureUrl' => $pictureUrl
        );
    }

    public function avatarCropAction(Request $request, $id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if($request->getMethod() == 'POST') {
            $options = $request->request->all();
            $filename = $request->query->get('filename');
            $filename = str_replace('!', '.', $filename);
            $filename = str_replace(array('..' , '/', '\\'), '', $filename);
            $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;
            $this->getUserService()->changeAvatar($id, realpath($pictureFilePath), $options);

            // return $this->redirect($this->generateUrl('admin_user'));
            return $this->createJsonResponse(true);
        }

        
    }

    public function lockAction($id)
    {
        $this->getUserService()->lockUser($id);
        return $this->render('TopxiaAdminBundle:User:user-table-tr.html.twig', array(
            'user' => $this->getUserService()->getUser($id),
        ));
    }

    public function unlockAction($id)
    {
        $this->getUserService()->unlockUser($id);

        return $this->render('TopxiaAdminBundle:User:user-table-tr.html.twig', array(
            'user' => $this->getUserService()->getUser($id),
        ));
    }

    public function sendPasswordResetEmailAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        if (empty($user)) {
            throw $this->createNotFoundException();
        }

        $token = $this->getUserService()->makeToken('password-reset', $user['id'], strtotime('+1 day'));

        try {
            $this->sendEmail(
                $user['email'],
                "重设{$user['nickname']}在{$this->setting('site.name', 'EDUSOHO')}的密码",
                $this->renderView('TopxiaWebBundle:PasswordReset:reset.txt.twig', array(
                    'user' => $user,
                    'token' => $token,
                )), 'html'
            );
            $this->getLogService()->info('user', 'send_password_reset', "管理员给用户 ${user['nickname']}({$user['id']}) 发送密码重置邮件");
        } catch(\Exception $e) {
            $this->getLogService()->error('user', 'send_password_reset', "管理员给用户 ${user['nickname']}({$user['id']}) 发送密码重置邮件失败：". $e->getMessage());
            throw $e;
        }


        return $this->createJsonResponse(true);
    }

    public function sendEmailVerifyEmailAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        if (empty($user)) {
            throw $this->createNotFoundException();
        }

        $token = $this->getUserService()->makeToken('email-verify', $user['id'], strtotime('+1 day'));
        $auth = $this->getSettingService()->get('auth', array());
        try {
            $this->sendEmail(
                $user['email'],
                "请激活你的帐号，完成注册",
                $this->renderView('TopxiaWebBundle:Register:email-verify.txt.twig', array(
                    'user' => $user,
                    'token' => $token,
                ))
            );
            $this->getLogService()->info('user', 'send_email_verify', "管理员给用户 ${user['nickname']}({$user['id']}) 发送Email验证邮件");
        } catch(\Exception $e) {
            $this->getLogService()->error('user', 'send_email_verify', "管理员给用户 ${user['nickname']}({$user['id']}) 发送Email验证邮件失败：". $e->getMessage());
            throw $e;
        }

        return $this->createJsonResponse(true);
    }

    public function changePasswordAction(Request $request, $userId)
    {
        $currentUser = $this->getCurrentUser();
        $user = $this->getUserService()->getUser($userId);
        if(!in_array('ROLE_SUPER_ADMIN', $currentUser['roles'])){
            throw $this->createAccessDeniedException();
        }
        
        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $this->getAuthService()->changePassword($user['id'], null, $formData['newPassword']);
            return $this->createJsonResponse(true);
        }
        
        return $this->render('TopxiaAdminBundle:User:change-password-modal.html.twig', array(
            'user' => $user
        ));

    }

    public function importUserDataToBaseAction(Request $request)
    {   
        $userData=$request->request->get("data");
        $userData=unserialize($userData);
        $checkType=$request->request->get("checkType");

        if($checkType=="ignore"){

            foreach ($userData as $key => $user) {
                if ($user["gender"]=="男")$user["gender"]="male";
                if ($user["gender"]=="女")$user["gender"]="female";
                if ($user["gender"]=="")$user["gender"]="secret";
                $this->getUserService()->register($user);
                }       
        }
        if($checkType=="update"){
   
            foreach ($userData as $key => $user) {
                if ($user["gender"]=="男")$user["gender"]="male";
                if ($user["gender"]=="女")$user["gender"]="female";
                if ($user["gender"]=="")$user["gender"]="secret";

                if($this->getUserService()->getUserByNickname($user["nickname"])){
                    $member=$this->getUserService()->getUserByNickname($user["nickname"]);
                    $this->getUserService()->changePassword($member["id"],$user["password"]);
                    $this->getUserService()->updateUserProfile($member["id"],$user);
                }
                elseif ($this->getUserService()->getUserByEmail($user["email"])){
                    $member=$this->getUserService()->getUserByEmail($user["email"]);
                    $this->getUserService()->changePassword($member["id"],$user["password"]);
                    $this->getUserService()->updateUserProfile($member["id"],$user);
                }else { 
                    $this->getUserService()->register($user);
                }          
            }       
        }
        return $this->render('TopxiaAdminBundle:User:userinfo.excel.step3.html.twig', array(
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

            if(!$file){
                $this->setFlashMessage('danger', '请选择上传的文件');

                return $this->render('TopxiaAdminBundle:User:userinfo.excel.html.twig', array(
                ));
            }
            if (FileToolkit::validateFileExtension($file,'xls xlsx')) {

                $this->setFlashMessage('danger', 'Excel格式不正确！');

                return $this->render('TopxiaAdminBundle:User:userinfo.excel.html.twig', array(
                ));
            }

            $objPHPExcel = PHPExcel_IOFactory::load($file);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            $highestRow = $objWorksheet->getHighestRow(); 

            $highestColumn = $objWorksheet->getHighestColumn();
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);   
            $fieldArray=$this->getFieldArray();

            for ($col = 0;$col < $highestColumnIndex;$col++)
            {
                 $fieldTitle=$objWorksheet->getCellByColumnAndRow($col, 2)->getValue();
                 $strs[$col]=$fieldTitle."";
            }   
            $excelField=$strs;
            if(!$this->checkNecessaryFields($excelField)){

                $this->setFlashMessage('danger', '缺少必要的字段');

                return $this->render('TopxiaAdminBundle:User:userinfo.excel.html.twig', array(
                ));
            }

            $fieldSort=$this->getFieldSort($excelField,$fieldArray);
            unset($fieldArray,$excelField);
            $repeatInfo=$this->checkRepeatData($row=3,$fieldSort,$highestRow,$objWorksheet);

            if($repeatInfo){

                $errorInfo[]=$repeatInfo;
                return $this->render('TopxiaAdminBundle:User:userinfo.excel.step2.html.twig', array(
                    "errorInfo"=>$errorInfo,
                ));

            }

            for ($row = 3;$row <= $highestRow;$row++) 
            {
                $strs=array();

                for ($col = 0;$col < $highestColumnIndex;$col++)
                {
                     $infoData=$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                     $strs[$col]=$infoData."";
                     unset($infoData);
                }    

                foreach ($fieldSort as $sort) {

                    $num=$sort['num'];
                    $key=$sort['fieldName'];

                    $userData[$key]=$strs[$num];
                    $fieldCol[$key]=$num+1;
                }
                unset($strs);

                $emptyData=array_count_values($userData);
                if(isset($emptyData[""])&&count($userData)==$emptyData[""]) {
                    $checkInfo[]="第".$row."行为空行，已跳过";
                    continue;
                }

                if($this->validFields($userData,$row,$fieldCol)){  
                    $errorInfo=array_merge($errorInfo,$this->validFields($userData,$row,$fieldCol));
                    continue;
                }

                for($i=1;$i<=5;$i++){
                    if (isset($userData['dateField'.$i])&&$userData['dateField'.$i]!=""){
                    $userData['dateField'.$i]=$this->excelTime($userData['dateField'.$i]);
                     }
                }   

                if(!$this->getUserService()->isNicknameAvaliable($userData['nickname'])){ 

                    if($checkType=="ignore") {
                        $checkInfo[]="第".$row."行的用户已存在，已略过"; 
                        continue;
                    }
                    if($checkType=="update") {
                        $checkInfo[]="第".$row."行的用户已存在，将会更新";          
                    }
                    $userCount=$userCount+1; 
                    $allUserData[]= $userData;            
                    continue;
                }
                if(!$this->getUserService()->isEmailAvaliable($userData['email'])){          

                    if($checkType=="ignore") {
                        $checkInfo[]="第".$row."行的用户已存在，已略过";
                        continue;
                    };
                    if($checkType=="update") {
                        $checkInfo[]="第".$row."行的用户已存在，将会更新";
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

            return $this->render('TopxiaAdminBundle:User:userinfo.excel.step2.html.twig', array(
                'userCount'=>$userCount,
                'errorInfo'=>$errorInfo,
                'checkInfo'=>$checkInfo,
                'allUserData'=>$allUserData,
                'checkType'=>$checkType,
            ));

        }

        return $this->render('TopxiaAdminBundle:User:userinfo.excel.html.twig', array(
        ));
    }

    private function validFields($userData,$row,$fieldCol)
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
        for($i=1;$i<=5;$i++){
            if (isset($userData['intField'.$i])&&$userData['intField'.$i]!=""&& !SimpleValidator::integer($userData['intField'.$i])){
            $errorInfo[]="第 ".$row."行".$fieldCol["intField".$i]." 列 的数据存在问题，请检查(必须为整数,最大到9位整数)。";
             }
            if (isset($userData['floatField'.$i])&&$userData['floatField'.$i]!=""&& !SimpleValidator::float($userData['floatField'.$i])){
            $errorInfo[]="第 ".$row."行".$fieldCol["floatField".$i]." 列 的数据存在问题，请检查(只保留到两位小数)。";
             }
            if (isset($userData['dateField'.$i])&&$userData['dateField'.$i]!=""&& !SimpleValidator::date($this->excelTime($userData['dateField'.$i]))){
            $errorInfo[]="第 ".$row."行".$fieldCol["dateField".$i]." 列 的数据存在问题，请检查(格式如XXXX-MM-DD)。";
             }
        }
        return $errorInfo;
    }

    private function checkRepeatData($row,$fieldSort,$highestRow,$objWorksheet)
    {
        $errorInfo=array();
        $emailData=array();
        $nicknameData=array();

        foreach ($fieldSort as $key => $value) {
            if($value["fieldName"]=="nickname"){
                $nickNameCol=$value["num"];
            }
            if($value["fieldName"]=="email"){
                $emailCol=$value["num"];
            }
        }

        for ($row ;$row <= $highestRow;$row++) {

            $emailColData =$objWorksheet->getCellByColumnAndRow($emailCol, $row)->getValue(); 
            if($emailColData.""=="") continue;
            $emailData[]=$emailColData."";         
        }

        $errorInfo=$this->arrayRepeat($emailData);

        for ($row=3 ;$row <= $highestRow;$row++) {

            $nickNameColData=$objWorksheet->getCellByColumnAndRow($nickNameCol, $row)->getValue();      
            if($nickNameColData.""=="") continue;
            $nicknameData[]=$nickNameColData.""; 
        }

        $errorInfo.=$this->arrayRepeat($nicknameData);

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
                $row=array_search($key, $array)+3;
                $repeatRow.="第".$row."行"."    ".$key."<br>";
                unset($array[$row-3]);
               }
            }
        }

        return $repeatRow;
    }

    private function excelTime($days, $time=false){
        if(is_numeric($days)){
            $jd = GregorianToJD(1, 1, 1970);
            $gregorian = JDToGregorian($jd+intval($days)-25569);
            $myDate = explode('/',$gregorian);
            $myDateStr = str_pad($myDate[2],4,'0', STR_PAD_LEFT)
                    ."-".str_pad($myDate[0],2,'0', STR_PAD_LEFT)
                    ."-".str_pad($myDate[1],2,'0', STR_PAD_LEFT)
                    .($time?" 00:00:00":'');
            return $myDateStr;
        }
        return $days;
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

    private function checkNecessaryFields($data)
    {       
        $data=implode("", $data);
        $data=$this->trim($data);
        $tmparray = explode("用户名",$data);
        if (count($tmparray)<=1) return false; 

        $tmparray = explode("邮箱",$data);
        if (count($tmparray)<=1) return false; 

        $tmparray = explode("密码",$data);
        if (count($tmparray)<=1) return false; 

        return true;
    }

    private function getFieldArray()
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


    private function trim($data)
    {       
        $data=trim($data);
        $data=str_replace(" ","",$data);
        $data=str_replace('\n','',$data);
        $data=str_replace('\r','',$data);
        $data=str_replace('\t','',$data);

        return $data;
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }
}