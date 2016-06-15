<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\MailFactory;
use Topxia\WebBundle\DataDict\UserRoleDict;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user   = $this->getCurrentUser();
        $fields = $request->query->all();

        $conditions = array(
            'roles'           => '',
            'keywordType'     => '',
            'keyword'         => '',
            'keywordUserType' => ''
        );

        $conditions = array_merge($conditions, $fields);
        $conditions = $this->fillOrgCode($conditions);

        $userCount = $this->getUserService()->searchUserCount($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $userCount,
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

//根据mobile查询user_profile获得userIds

        if (isset($conditions['keywordType']) && $conditions['keywordType'] == 'verifiedMobile' && !empty($conditions['keyword'])) {
            $profilesCount = $this->getUserService()->searchUserProfileCount(array('mobile' => $conditions['keyword']));
            $userProfiles  = $this->getUserService()->searchUserProfiles(
                array('mobile' => $conditions['keyword']),
                array('id', 'DESC'),
                0,
                $profilesCount
            );
            $userIds       = ArrayToolkit::column($userProfiles, 'id');

            if (!empty($userIds)) {
                unset($conditions['keywordType']);
                unset($conditions['keyword']);
                $conditions['userIds'] = array_merge(ArrayToolkit::column($users, 'userId'), $userIds);
            }

            $userCount = $this->getUserService()->searchUserCount($conditions);
            $paginator = new Paginator(
                $this->get('request'),
                $userCount,
                20
            );

            $users = $this->getUserService()->searchUsers(
                $conditions,
                array('createdTime', 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }

        $app = $this->getAppService()->findInstallApp("UserImporter");

        $showUserExport = false;

        if (!empty($app) && array_key_exists('version', $app)) {
            $showUserExport = version_compare($app['version'], "1.0.2", ">=");
        }

        $userIds  = ArrayToolkit::column($users, 'id');
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

        return $this->render('TopxiaAdminBundle:User:index.html.twig', array(
            'users'          => $users,
            'userCount'      => $userCount,
            'paginator'      => $paginator,
            'profiles'       => $profiles,
            'showUserExport' => $showUserExport
        ));
    }

    public function emailCheckAction(Request $request)
    {
        $email = $request->query->get('value');
        $email = str_replace('!', '.', $email);
        list($result, $message) = $this->getAuthService()->checkEmail($email);
        return $this->validateResult($result, $message);
    }

    public function mobileCheckAction(Request $request)
    {
        $mobile = $request->query->get('value');
        $mobile = str_replace('!', '.', $mobile);
        list($result, $message) = $this->getAuthService()->checkMobile($mobile);
        return $this->validateResult($result, $message);
    }

    public function nicknameCheckAction(Request $request)
    {
        $nickname = $request->query->get('value');
        list($result, $message) = $this->getAuthService()->checkUsername($nickname);
        return $this->validateResult($result, $message);
    }

    public function emailOrMobileCheckAction(Request $request)
    {
        $emailOrMobile = $request->query->get('value');
        $emailOrMobile = str_replace('!', '.', $emailOrMobile);
        list($result, $message) = $this->getAuthService()->checkEmailOrMobile($emailOrMobile);
        return $this->validateResult($result, $message);
    }

    protected function validateResult($result, $message)
    {
        if ($result == 'success') {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => $message);
        }

        return $this->createJsonResponse($response);
    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $formData         = $request->request->all();
            $formData['type'] = 'import';
            $registration     = $this->getRegisterData($formData, $request->getClientIp());
            $user             = $this->getAuthService()->register($registration);

            $this->get('session')->set('registed_email', $user['email']);

            if (isset($formData['roles'])) {
                $roles[] = 'ROLE_TEACHER';
                array_push($roles, 'ROLE_USER');
                $this->getUserService()->changeUserRoles($user['id'], $roles);
            }

            $this->getLogService()->info('user', 'add', "管理员添加新用户 {$user['nickname']} ({$user['id']})");

            return $this->redirect($this->generateUrl('admin_user'));
        }

        return $this->render($this->getCreateUserModal());
    }

    protected function getRegisterData($formData, $clientIp)
    {
        if (isset($formData['email'])) {
            $userData['email'] = $formData['email'];
        }

        if (isset($formData['emailOrMobile'])) {
            $userData['emailOrMobile'] = $formData['emailOrMobile'];
        }

        if (isset($formData['mobile'])) {
            $userData['mobile'] = $formData['mobile'];
        }

        $userData['nickname']  = $formData['nickname'];
        $userData['password']  = $formData['password'];
        $userData['createdIp'] = $clientIp;
        $userData['type']      = $formData['type'];

        if(isset($formData['orgCode'])){
            $userData['orgCode']  = $formData['orgCode'];
        }

        return $userData;
    }

    protected function getCreateUserModal()
    {
        $auth = $this->getSettingService()->get('auth');

        if (isset($auth['register_mode']) && $auth['register_mode'] == 'email_or_mobile') {
            return 'TopxiaAdminBundle:User:create-by-mobile-or-email-modal.html.twig';
        } elseif (isset($auth['register_mode']) && $auth['register_mode'] == 'mobile') {
            return 'TopxiaAdminBundle:User:create-by-mobile-modal.html.twig';
        } else {
            return 'TopxiaAdminBundle:User:create-modal.html.twig';
        }
    }

    public function editAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        $profile          = $this->getUserService()->getUserProfile($user['id']);
        $profile['title'] = $user['title'];

        if ($request->getMethod() == 'POST') {
            $profile = $request->request->all();

            if (!((strlen($user['verifiedMobile']) > 0) && isset($profile['mobile']))) {
                $profile = $this->getUserService()->updateUserProfile($user['id'], $profile);
                $this->getLogService()->info('user', 'edit', "管理员编辑用户资料 {$user['nickname']} (#{$user['id']})", $profile);
            } else {
                $this->setFlashMessage('danger', '用户已绑定的手机不能修改。');
            }

            return $this->redirect($this->generateUrl('admin_user'));
        }

        $fields = $this->getFields();

        return $this->render('TopxiaAdminBundle:User:edit-modal.html.twig', array(
            'user'    => $user,
            'profile' => $profile,
            'fields'  => $fields
        ));
    }

    public function orgUpdateAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        if ($request->isMethod('POST')) {
            $orgCode = $request->request->get('orgCode', $user['orgCode']);
            $this->getUserService()->changeUserOrg($user['id'], $orgCode);
        }

        $org = $this->getOrgService()->getOrgByOrgCode($user['orgCode']);
        return $this->render('TopxiaAdminBundle:User:update-org-modal.html.twig', array(
            'user' => $user,
            'org'  => $org
        ));
    }

    public function showAction(Request $request, $id)
    {
        $user             = $this->getUserService()->getUser($id);
        $profile          = $this->getUserService()->getUserProfile($id);
        $profile['title'] = $user['title'];

        $fields = $this->getFields();
        return $this->render('TopxiaAdminBundle:User:show-modal.html.twig', array(
            'user'    => $user,
            'profile' => $profile,
            'fields'  => $fields
        ));
    }

    public function rolesAction(Request $request, $id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')
            && false === $this->get('security.context')->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException();
        }

        $user        = $this->getUserService()->getUser($id);
        $currentUser = $this->getCurrentUser();

        if ($request->getMethod() == 'POST') {
            $roles = $request->request->get('roles');

            $this->getUserService()->changeUserRoles($user['id'], $roles);

            $dataDict     = new UserRoleDict();
            $roleDict     = $dataDict->getDict();
            $role         = "";
            $roleCount    = count($roles);
            $deletedRoles = array_diff($user['roles'], $roles);
            $addedRoles   = array_diff($roles, $user['roles']);

            if (!empty($deletedRoles) || !empty($addedRoles)) {
                for ($i = 0; $i < $roleCount; $i++) {
                    $role .= $roleDict[$roles[$i]];

                    if ($i < $roleCount - 1) {
                        $role .= "、";
                    }
                }

                $message = array(
                    'userId'   => $currentUser['id'],
                    'userName' => $currentUser['nickname'],
                    'role'     => $role);
                $this->getNotifiactionService()->notify($user['id'], 'role', $message);
            }

            if (in_array('ROLE_TEACHER', $user['roles']) && !in_array('ROLE_TEACHER', $roles)) {
                $this->getCourseService()->cancelTeacherInAllCourses($user['id']);
            }

            $user = $this->getUserService()->getUser($id);
            return $this->render('TopxiaAdminBundle:User:user-table-tr.html.twig', array(
                'user'    => $user,
                'profile' => $this->getUserService()->getUserProfile($id)
            ));
        }

        $default = $this->getSettingService()->get('default', array());
        return $this->render('TopxiaAdminBundle:User:roles-modal.html.twig', array(
            'user'    => $user,
            'default' => $default
        ));
    }

    public function avatarAction(Request $request, $id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUserService()->getUser($id);

        $hasPartnerAuth = $this->getAuthService()->hasPartnerAuth();

        if ($hasPartnerAuth) {
            $partnerAvatar = $this->getAuthService()->getPartnerAvatar($user['id'], 'big');
        } else {
            $partnerAvatar = null;
        }

        return $this->render('TopxiaAdminBundle:User:user-avatar-modal.html.twig', array(
            'user'          => $user,
            'partnerAvatar' => $partnerAvatar
        ));
    }

    protected function getFields()
    {
        $fields = $this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();

        for ($i = 0; $i < count($fields); $i++) {
            if (strstr($fields[$i]['fieldName'], "textField")) {
                $fields[$i]['type'] = "text";
            }

            if (strstr($fields[$i]['fieldName'], "varcharField")) {
                $fields[$i]['type'] = "varchar";
            }

            if (strstr($fields[$i]['fieldName'], "intField")) {
                $fields[$i]['type'] = "int";
            }

            if (strstr($fields[$i]['fieldName'], "floatField")) {
                $fields[$i]['type'] = "float";
            }

            if (strstr($fields[$i]['fieldName'], "dateField")) {
                $fields[$i]['type'] = "date";
            }
        }

        return $fields;
    }

    public function avatarCropAction(Request $request, $id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUserService()->getUser($id);

        if ($request->getMethod() == 'POST') {
            $options = $request->request->all();
            $this->getUserService()->changeAvatar($id, $options["images"]);

            return $this->createJsonResponse(true);
        }

        $fileId = $request->getSession()->get("fileId");
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 270, 270);

        return $this->render('TopxiaAdminBundle:User:user-avatar-crop-modal.html.twig', array(
            'user'        => $user,
            'pictureUrl'  => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize'  => $scaledSize
        ));
    }

    public function lockAction($id)
    {
        $this->getUserService()->lockUser($id);
        $this->kickUserLogout($id);
        return $this->render('TopxiaAdminBundle:User:user-table-tr.html.twig', array(
            'user'    => $this->getUserService()->getUser($id),
            'profile' => $this->getUserService()->getUserProfile($id)
        ));
    }

    public function unlockAction($id)
    {
        $this->getUserService()->unlockUser($id);

        return $this->render('TopxiaAdminBundle:User:user-table-tr.html.twig', array(
            'user'    => $this->getUserService()->getUser($id),
            'profile' => $this->getUserService()->getUserProfile($id)
        ));
    }

    public function sendPasswordResetEmailAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        if (empty($user)) {
            throw $this->createNotFoundException();
        }

        $token = $this->getUserService()->makeToken('password-reset', $user['id'], strtotime('+1 day'));
        $site   = $this->setting('site', array());
        try {
            $mailOptions = array(
                'to'     => $user['email'],
                'template' => 'email_reset_password',
                'params' => array(
                    'nickname' => $user['nickname'],
                    'verifyurl' => $this->generateUrl('password_reset_update', array('token' => $token), true),
                    'sitename' => $site['name'],
                    'siteurl' => $site['url']
                )
            );
            $mail = MailFactory::create($mailOptions);
            $mail->send();
            $this->getLogService()->info('user', 'send_password_reset', "管理员给用户 ${user['nickname']}({$user['id']}) 发送密码重置邮件");
        } catch (\Exception $e) {
            $this->getLogService()->error('user', 'send_password_reset', "管理员给用户 ${user['nickname']}({$user['id']}) 发送密码重置邮件失败：" . $e->getMessage());
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

        $site      = $this->getSettingService()->get('site', array());
        $verifyurl         = $this->generateUrl('register_email_verify', array('token' => $token), true);

        try {
            $mailOptions  = array(
                'to'        => $user['email'],
                'template'  => 'email_registration',
                'params' => array(
                    'sitename' => $site['name'],
                    'siteurl' => $site['url'],
                    'verifyurl' => $verifyurl,
                    'nickname'  => $user['nickname']
                ),
            );

            $mail = MailFactory::create($mailOptions);
            $mail->send();
            $this->getLogService()->info('user', 'send_email_verify', "管理员给用户 ${user['nickname']}({$user['id']}) 发送Email验证邮件");
        } catch (\Exception $e) {
            $this->getLogService()->error('user', 'send_email_verify', "管理员给用户 ${user['nickname']}({$user['id']}) 发送Email验证邮件失败：" . $e->getMessage());
            throw $e;
        }

        return $this->createJsonResponse(true);
    }

    public function changePasswordAction(Request $request, $userId)
    {
        $currentUser = $this->getCurrentUser();
        $user        = $this->getUserService()->getUser($userId);

        if (!in_array('ROLE_SUPER_ADMIN', $currentUser['roles'])) {
            throw $this->createAccessDeniedException();
        }

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $this->getAuthService()->changePassword($user['id'], null, $formData['newPassword']);
            $this->kickUserLogout($user['id']);
            return $this->createJsonResponse(true);
        }

        return $this->render('TopxiaAdminBundle:User:change-password-modal.html.twig', array(
            'user' => $user
        ));
    }

    protected function kickUserLogout($userId)
    {
        $this->getSessionService()->clearByUserId($userId);
        $tokens = $this->getTokenService()->findTokensByUserIdAndType($userId, 'mobile_login');
        if (!empty($tokens)) {
            foreach ($tokens as $token) {
                $this->getTokenService()->destoryToken($token['token']);
            }
        }
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

    protected function getSessionService()
    {
        return $this->getServiceKernel()->createService('System.SessionService');
    }

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
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

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    protected function getOrgService()
    {
        return $this->getServiceKernel()->createService('Org:Org.OrgService');
    }
}
