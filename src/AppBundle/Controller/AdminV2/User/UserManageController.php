<?php

namespace AppBundle\Controller\AdminV2\User;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\Service\AppService;
use Biz\Content\Service\FileService;
use Biz\Course\Service\CourseService;
use Biz\Org\Service\OrgService;
use Biz\Role\Service\RoleService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Service\AuthService;
use Biz\User\Service\NotificationService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserFieldService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserManageController extends BaseController
{
    private $keywordType = array('verifiedMobile', 'idcard');

    public function indexAction(Request $request)
    {
        $fields = $request->query->all();

        $conditions = array(
            'roles' => '',
            'keywordType' => '',
            'keyword' => '',
            'keywordUserType' => '',
            'destroyed' => 0,
        );

        $conditions = array_merge($conditions, $fields);
        $conditions = $this->fillOrgCode($conditions);

        $userCount = $this->getUserService()->countUsers($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $userCount,
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        //根据mobile或者idcard查询user_profile获得userIds
        if (isset($conditions['keywordType']) && in_array($conditions['keywordType'], $this->keywordType) && !empty($conditions['keyword'])) {
            $preConditions = $this->getUserProfileConditions($conditions);
            $profilesCount = $this->getUserService()->searchUserProfileCount($preConditions);
            $userProfiles = $this->getUserService()->searchUserProfiles(
                $preConditions,
                array('id' => 'DESC'),
                0,
                $profilesCount
            );
            $userIds = ArrayToolkit::column($userProfiles, 'id');

            if (!empty($userIds)) {
                unset($conditions['keywordType']);
                unset($conditions['keyword']);
                $conditions['userIds'] = array_merge(ArrayToolkit::column($users, 'userId'), $userIds);
            } elseif ('idcard' == $conditions['keywordType']) {
                unset($conditions['keywordType']);
                unset($conditions['keyword']);
                $conditions['userIds'] = empty($userIds) ? array(0) : $userIds;
            }

            $userCount = $this->getUserService()->countUsers($conditions);
            $paginator = new Paginator(
                $this->get('request'),
                $userCount,
                20
            );

            $users = $this->getUserService()->searchUsers(
                $conditions,
                array('createdTime' => 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }

        $app = $this->getAppService()->findInstallApp('UserImporter');

        $showUserExport = false;

        if (!empty($app) && array_key_exists('version', $app)) {
            $showUserExport = version_compare($app['version'], '1.0.2', '>=');
        }

        $userIds = ArrayToolkit::column($users, 'id');
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

        $allRoles = $this->getAllRoles();

        return $this->render('admin-v2/user/user-manage/index.html.twig', array(
            'users' => $users,
            'allRoles' => $allRoles,
            'userCount' => $userCount,
            'paginator' => $paginator,
            'profiles' => $profiles,
            'showUserExport' => $showUserExport,
        ));
    }

    protected function getAllRoles()
    {
        $roles = $this->getRoleService()->searchRoles(array(), 'created', 0, PHP_INT_MAX);

        $roleDicts = array();
        foreach ($roles as $role) {
            $roleDicts[$role['code']] = $role['name'];
        }

        return $roleDicts;
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
        if ('success' === $result) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => $message);
        }

        return $this->createJsonResponse($response);
    }

    public function createAction(Request $request)
    {
        if ('POST' === $request->getMethod()) {
            $formData = $request->request->all();
            $formData['type'] = 'import';
            $registration = $this->getRegisterData($formData, $request->getClientIp());

            $user = $this->getAuthService()->register($registration);

            $this->get('session')->set('registed_email', $user['email']);

            if (isset($formData['roles'])) {
                $roles[] = 'ROLE_TEACHER';
                array_push($roles, 'ROLE_USER');
                $this->getUserService()->changeUserRoles($user['id'], $roles);
            }

            $this->getLogService()->info('user', 'add', "管理员添加新用户 {$user['nickname']} ({$user['id']})");

            return $this->redirect($this->generateUrl('admin_v2_user'));
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

        $userData['nickname'] = $formData['nickname'];
        $userData['password'] = $formData['password'];
        $userData['createdIp'] = $clientIp;
        $userData['type'] = $formData['type'];
        $userData['passwordInit'] = 0;

        if (isset($formData['orgCode'])) {
            $userData['orgCode'] = $formData['orgCode'];
        }

        return $userData;
    }

    protected function getCreateUserModal()
    {
        $auth = $this->getSettingService()->get('auth');

        if (isset($auth['register_mode']) && 'email_or_mobile' == $auth['register_mode']) {
            return 'admin-v2/user/user-manage/create-by-mobile-or-email-modal.html.twig';
        } elseif (isset($auth['register_mode']) && 'mobile' == $auth['register_mode']) {
            return 'admin-v2/user/user-manage/create-by-mobile-modal.html.twig';
        } else {
            return 'admin-v2/user/user-manage/create-modal.html.twig';
        }
    }

    public function editAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        $profile = $this->getUserService()->getUserProfile($user['id']);
        $profile['title'] = $user['title'];

        if ('POST' === $request->getMethod()) {
            $profile = $request->request->all();

            if (!((strlen($user['verifiedMobile']) > 0) && isset($profile['mobile']))) {
                $this->getUserService()->updateUserProfile($user['id'], $profile, false);
            } else {
                $this->setFlashMessage('danger', 'user.settings.profile.unable_change_bind_mobile');
            }

            return $this->redirect($this->generateUrl('admin_v2_user'));
        }

        $fields = $this->getFields();

        return $this->render('admin-v2/user/user-manage/edit-modal.html.twig', array(
            'user' => $user,
            'profile' => $profile,
            'fields' => $fields,
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

        return $this->render('admin-v2/user/user-manage/update-org-modal.html.twig', array(
            'user' => $user,
            'org' => $org,
        ));
    }

    public function showAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        if ($user['destroyed'] == 1) {
            return $this->render('admin-v2/user/user-manage/show-destroyed-modal.html.twig', array());
        }
        $profile = $this->getUserService()->getUserProfile($id);
        $profile['title'] = $user['title'];

        $fields = $this->getFields();

        return $this->render('admin-v2/user/user-manage/show-modal.html.twig', array(
            'user' => $user,
            'profile' => $profile,
            'fields' => $fields,
        ));
    }

    public function rolesAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $currentUser = $this->getUser();

        if ('POST' === $request->getMethod()) {
            $roles = $request->request->get('roles');

            $this->getUserService()->changeUserRoles($user['id'], $roles);

            if (!empty($roles)) {
                $roleSet = $this->getRoleService()->searchRoles(array(), 'created', 0, 9999);
                $rolesByIndexCode = ArrayToolkit::index($roleSet, 'code');
                $roleNames = $this->getRoleNames($roles, $rolesByIndexCode);

                $message = array(
                    'userId' => $currentUser['id'],
                    'userName' => $currentUser['nickname'],
                    'role' => implode(',', $roleNames),
                );

                $this->getNotifiactionService()->notify($user['id'], 'role', $message);
            }
            $user = $this->getUserService()->getUser($id);

            return $this->render('admin-v2/user/user-manage/user-table-tr.html.twig', array(
                'user' => $user,
                'profile' => $this->getUserService()->getUserProfile($id),
            ));
        }

        return $this->render('admin-v2/user/user-manage/roles-modal.html.twig', array(
            'user' => $user,
        ));
    }

    public function updateNicknameCheckAction(Request $request, $userId)
    {
        $nickname = $request->query->get('value');
        $user = $this->getUserService()->getUser($userId);

        if ($user['nickname'] == $nickname) {
            return $this->createJsonResponse(array('success' => true, 'message' => ''));
        }

        list($result, $message) = $this->getAuthService()->checkUsername($nickname);

        return $this->validateResult($result, $message);
    }

    protected function getRoleNames($roles, $roleSet)
    {
        $roleNames = array();
        $roles = array_unique($roles);

        $userRoleDict = $this->get('codeages_plugin.dict_twig_extension')->getDict('userRole');

        $roleDictCodes = array_keys($userRoleDict);

        foreach ($roles as $role) {
            if (in_array($role, $roleDictCodes)) {
                $roleNames[] = $userRoleDict[$role];
            } elseif ('ROLE_BACKEND' === $role) {
                continue;
            } else {
                $role = $roleSet[$role];
                $roleNames[] = $role['name'];
            }
        }

        return $roleNames;
    }

    public function avatarAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        $hasPartnerAuth = $this->getAuthService()->hasPartnerAuth();

        if ($hasPartnerAuth) {
            $partnerAvatar = $this->getAuthService()->getPartnerAvatar($user['id'], 'big');
        } else {
            $partnerAvatar = null;
        }

        return $this->render('admin-v2/user/user-manage/user-avatar-modal.html.twig', array(
            'user' => $user,
            'partnerAvatar' => $partnerAvatar,
        ));
    }

    protected function getFields()
    {
        $fields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        for ($i = 0; $i < count($fields); ++$i) {
            if (strstr($fields[$i]['fieldName'], 'textField')) {
                $fields[$i]['type'] = 'text';
            }

            if (strstr($fields[$i]['fieldName'], 'varcharField')) {
                $fields[$i]['type'] = 'varchar';
            }

            if (strstr($fields[$i]['fieldName'], 'intField')) {
                $fields[$i]['type'] = 'int';
            }

            if (strstr($fields[$i]['fieldName'], 'floatField')) {
                $fields[$i]['type'] = 'float';
            }

            if (strstr($fields[$i]['fieldName'], 'dateField')) {
                $fields[$i]['type'] = 'date';
            }
        }

        return $fields;
    }

    public function avatarCropAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        if ('POST' === $request->getMethod()) {
            $options = $request->request->all();
            $this->getUserService()->changeAvatar($id, $options['images']);

            return $this->createJsonResponse(true);
        }

        $fileId = $request->getSession()->get('fileId');
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 270, 270);

        return $this->render('admin-v2/user/user-manage/user-avatar-crop-modal.html.twig', array(
            'user' => $user,
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    public function lockAction($id)
    {
        $this->getUserService()->lockUser($id);
        $this->kickUserLogout($id);

        return $this->render('admin-v2/user/user-manage/user-table-tr.html.twig', array(
            'user' => $this->getUserService()->getUser($id),
            'profile' => $this->getUserService()->getUserProfile($id),
        ));
    }

    public function unlockAction($id)
    {
        $this->getUserService()->unlockUser($id);

        return $this->render('admin-v2/user/user-manage/user-table-tr.html.twig', array(
            'user' => $this->getUserService()->getUser($id),
            'profile' => $this->getUserService()->getUserProfile($id),
        ));
    }

    public function sendPasswordResetEmailAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $token = $this->getUserService()->makeToken('password-reset', $user['id'], strtotime('+1 day'));
        $site = $this->setting('site', array());
        try {
            $mailOptions = array(
                'to' => $user['email'],
                'template' => 'email_reset_password',
                'params' => array(
                    'nickname' => $user['nickname'],
                    'verifyurl' => $this->generateUrl('password_reset_update', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL),
                    'sitename' => $site['name'],
                    'siteurl' => $site['url'],
                ),
            );
            $mailFactory = $this->getBiz()->offsetGet('mail_factory');
            $mail = $mailFactory($mailOptions);
            $mail->send();
            $this->getLogService()->info('user', 'password-reset', "管理员给用户 ${user['nickname']}({$user['id']}) 发送密码重置邮件");
        } catch (\Exception $e) {
            $this->getLogService()->error('user', 'password-reset', "管理员给用户 ${user['nickname']}({$user['id']}) 发送密码重置邮件失败：".$e->getMessage());
            throw $e;
        }

        return $this->createJsonResponse(true);
    }

    public function sendEmailVerifyEmailAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $token = $this->getUserService()->makeToken('email-verify', $user['id'], strtotime('+1 day'));

        $site = $this->getSettingService()->get('site', array());
        $verifyurl = $this->generateUrl('register_email_verify', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

        try {
            $mailOptions = array(
                'to' => $user['email'],
                'template' => 'email_registration',
                'params' => array(
                    'sitename' => $site['name'],
                    'siteurl' => $site['url'],
                    'verifyurl' => $verifyurl,
                    'nickname' => $user['nickname'],
                ),
            );

            $mailFactory = $this->getBiz()->offsetGet('mail_factory');
            $mail = $mailFactory($mailOptions);
            $mail->send();
            $this->getLogService()->info('user', 'send_email_verify', "管理员给用户 {$user['nickname']}({$user['id']}) 发送Email验证邮件");
        } catch (\Exception $e) {
            $this->getLogService()->error('user', 'send_email_verify', "管理员给用户 {$user['nickname']}({$user['id']}) 发送Email验证邮件失败：".$e->getMessage());
            throw $e;
        }

        return $this->createJsonResponse(true);
    }

    public function changeNicknameAction(Request $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);

        if ('POST' === $request->getMethod()) {
            $formData = $request->request->all();
            $this->getAuthService()->changeNickname($user['id'], $formData['nickname']);
            $this->kickUserLogout($user['id']);

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/user/user-manage/change-nickname-modal.html.twig', array(
            'user' => $user,
        ));
    }

    public function changePasswordAction(Request $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);

        if ('POST' === $request->getMethod()) {
            $formData = $request->request->all();
            $this->getAuthService()->changePassword($user['id'], null, $formData['newPassword']);
            $this->kickUserLogout($user['id']);

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/user/user-manage/change-password-modal.html.twig', array(
            'user' => $user,
        ));
    }

    protected function kickUserLogout($userId)
    {
        $tokens = $this->getTokenService()->findTokensByUserIdAndType($userId, 'mobile_login');
        if (!empty($tokens)) {
            foreach ($tokens as $token) {
                $this->getTokenService()->destoryToken($token['token']);
            }
        }
    }

    protected function getUserProfileConditions($conditions)
    {
        if ('verifiedMobile' == $conditions['keywordType']) {
            return array('mobile' => $conditions['keyword']);
        } else {
            return array('idcard' => $conditions['keyword']);
        }
    }

    /**
     * @return RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Role:RoleService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotifiactionService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }
}
