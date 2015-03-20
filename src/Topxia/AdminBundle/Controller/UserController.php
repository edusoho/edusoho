<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
use Topxia\WebBundle\DataDict\UserRoleDict;

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

        if(empty($fields)){
            $fields = array();
        }

        $conditions = array_merge($conditions, $fields);

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

        $app = $this->getAppService()->findInstallApp("UserImporter");
        $enabled = $this->getSettingService()->get('plugin_userImporter_enabled');
        
        $showUserExport = false;
        if(!empty($app) && array_key_exists('version', $app) && $enabled){
            $showUserExport = version_compare($app['version'], "1.0.2", ">=");
        }

        return $this->render('TopxiaAdminBundle:User:index.html.twig', array(
            'users' => $users ,
            'paginator' => $paginator,
            'showUserExport' => $showUserExport
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
            $profile = $request->request->all();
            if (!( (strlen($user['verifiedMobile']) > 0) && isset($profile['mobile']) )) {
                $profile = $this->getUserService()->updateUserProfile($user['id'], $profile);
                $this->getLogService()->info('user', 'edit', "管理员编辑用户资料 {$user['nickname']} (#{$user['id']})", $profile);
            } else {
                $this->setFlashMessage('danger', '用户已绑定的手机不能修改。');
            }

            return $this->redirect($this->generateUrl('admin_user'));
        }

        $fields=$this->getFields();

        return $this->render('TopxiaAdminBundle:User:edit-modal.html.twig', array(
            'user' => $user,
            'profile'=>$profile,
            'fields'=>$fields,
        ));
    }

    public function showAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $profile = $this->getUserService()->getUserProfile($id);
        $profile['title'] = $user['title'];

        $fields=$this->getFields();
            
        return $this->render('TopxiaAdminBundle:User:show-modal.html.twig', array(
            'user' => $user,
            'profile' => $profile,
            'fields'=>$fields,
        ));
    }

    public function rolesAction(Request $request, $id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')
            and false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUserService()->getUser($id);
        $currentUser = $this->getCurrentUser();
        if ($request->getMethod() == 'POST') {
            $roles = $request->request->get('roles');

            $this->getUserService()->changeUserRoles($user['id'], $roles);

            $dataDict = new UserRoleDict();
            $roleDict = $dataDict->getDict();
            $role = "";
            $roleCount = count($roles);
            $deletedRoles = array_diff($user['roles'], $roles);
            $addedRoles = array_diff($roles, $user['roles']);
            if(!empty($deletedRoles) || !empty($addedRoles) ){
                for ($i=0; $i<$roleCount; $i++) {
                    $role .= $roleDict[$roles[$i]];
                    if ($i<$roleCount - 1){
                        $role .= "、";
                    }
                }
                $this->getNotifiactionService()->notify($user['id'],'default',"您被“{$currentUser['nickname']}”设置为“{$role}”身份。");
            }

            if (in_array('ROLE_TEACHER', $user['roles']) && !in_array('ROLE_TEACHER', $roles)) {
                $this->getCourseService()->cancelTeacherInAllCourses($user['id']);
            }

            $user = $this->getUserService()->getUser($id);
            return $this->render('TopxiaAdminBundle:User:user-table-tr.html.twig', array(
            'user' => $user,
            ));
        }
        $default = $this->getSettingService()->get('default', array());     
        return $this->render('TopxiaAdminBundle:User:roles-modal.html.twig', array(
            'user' => $user,
            'default'=> $default
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

    private function getFields()
    {
        $fields=$this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();
        for($i=0;$i<count($fields);$i++){
            if(strstr($fields[$i]['fieldName'], "textField")) $fields[$i]['type']="text";
            if(strstr($fields[$i]['fieldName'], "varcharField")) $fields[$i]['type']="varchar";
            if(strstr($fields[$i]['fieldName'], "intField")) $fields[$i]['type']="int";
            if(strstr($fields[$i]['fieldName'], "floatField")) $fields[$i]['type']="float";
            if(strstr($fields[$i]['fieldName'], "dateField")) $fields[$i]['type']="date";
        }

        return $fields;
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
        
        $site = $this->getSettingService()->get('site', array());
        $emailBody = $this->setting('auth.email_activation_body', ' 验证邮箱内容');

        $valuesToBeReplace = array('{{nickname}}', '{{sitename}}', '{{siteurl}}', '{{verifyurl}}');
        $verifyurl = $this->generateUrl('register_email_verify', array('token' => $token), true);
        $valuesToReplace = array($user['nickname'], $site['name'], $site['url'], $verifyurl);
        $emailBody = str_replace($valuesToBeReplace, $valuesToReplace, $emailBody);

        if (!empty($emailBody)) {
            $emailBody = $emailBody;
        }else{
             $emailBody = $this->renderView('TopxiaWebBundle:Register:email-verify.txt.twig', array(  
                'user' => $user,  
                'token' => $token,  
                )) ;
        }

        try {
            $this->sendEmail(
                $user['email'],
                '请激活你的帐号 完成注册',
                $emailBody
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

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getNotifiactionService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }
}