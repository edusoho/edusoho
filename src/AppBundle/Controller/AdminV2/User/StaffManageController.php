<?php

namespace AppBundle\Controller\AdminV2\User;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Common\SimpleValidator;
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

class StaffManageController extends UserCommonController
{
    private $route='admin_v2_staff';

    public function indexAction(Request $request)
    {
        $indexTwigUrl = 'admin-v2/user/user-manage/staff-manage/index.html.twig';
        $fields = $request->query->all();
        $conditions = [
            'rolesNot' => '|ROLE_USER|',
            'keywordType' => '',
            'keyword' => '',
            'keywordUserType' => '',
            'destroyed' => 0,
        ];

        return $this->index($fields, $conditions, $indexTwigUrl);
    }


    public function emailCheckAction(Request $request)
    {
        return $this->emailCheck($request);
    }

    public function mobileCheckAction(Request $request)
    {
        return $this->mobileCheck($request);
    }

    public function nicknameCheckAction(Request $request)
    {
        return $this->nicknameCheck($request);
    }

    public function emailOrMobileCheckAction(Request $request)
    {
        return $this->emailOrMobileCheck($request);
    }

    public function createAction(Request $request)
    {
        $isStaff = true;

        return $this->create($request,$this->route,$isStaff);
    }

    public function editAction(Request $request, $id)
    {
        $editTwigUrl = 'admin-v2/user/user-manage/staff-manage/edit-modal.html.twig';

        return $this->edit($request, $id, $this->route, $editTwigUrl);
    }

    public function orgUpdateAction(Request $request, $id)
    {
        $orgUpdateTwigUrl = 'admin-v2/user/user-manage/staff-manage/update-org-modal.html.twig';

        return $this->orgUpdate($request, $id, $orgUpdateTwigUrl);
    }

    public function showAction(Request $request, $id)
    {
        $showTwigUrlOne = 'admin-v2/user/user-manage/staff-manage/show-destroyed-modal.html.twig';
        $showTwigUrlTwo = 'admin-v2/user/user-manage/staff-manage/show-modal.html.twig';

        return $this->show($request, $id, $showTwigUrlOne, $showTwigUrlTwo);

    }

    public function rolesAction(Request $request, $id)
    {
        $rolesTwigUrlOne = 'admin-v2/user/user-manage/staff-manage/user-table-tr.html.twig';
        $rolesTwigUrlTwo = 'admin-v2/user/user-manage/staff-manage/roles-modal.html.twig';

        return $this->roles($request, $id, $rolesTwigUrlOne, $rolesTwigUrlTwo);
    }

    public function updateNicknameCheckAction(Request $request, $userId)
    {
        return $this->updateNicknameCheck($request, $userId);
    }


    public function avatarAction(Request $request, $id)
    {
        $avatarTwigUrl = 'admin-v2/user/user-manage/staff-manage/user-avatar-modal.html.twig';

        return $this->avatar($request, $id, $avatarTwigUrl);
    }

    public function qrCodeAction(Request $request, $id)
    {
        $qrCodeTwigUrl = 'admin-v2/user/user-manage/staff-manage/assistant-qrcode-modal.html.twig';

        return $this->qrCode($request, $id, $qrCodeTwigUrl);

    }

    public function avatarCropAction(Request $request, $id)
    {
        $avatarCropTwigUrl = 'admin-v2/user/user-manage/staff-manage/user-avatar-crop-modal.html.twig';

        return $this->avatarCrop($request, $id, $avatarCropTwigUrl);
    }

    public function assistantQrCodeCropAction(Request $request, $id)
    {
        $assistantQrCodeCropTwigUrl = 'admin-v2/user/user-manage/staff-manage/assistant-qrcode-crop-modal.html.twig';

        return $this->assistantQrCodeCrop($request, $id, $assistantQrCodeCropTwigUrl);
    }

    public function lockAction($id)
    {
        $lockTwigUrl = 'admin-v2/user/user-manage/staff-manage/user-table-tr.html.twig';

        return $this->lock($id, $lockTwigUrl);
    }

    public function unlockAction($id)
    {
        $unlockTwigUrl = 'admin-v2/user/user-manage/staff-manage/user-table-tr.html.twig';

        return $this->unlock($id, $unlockTwigUrl);
    }

    public function deleteAction($id)
    {
        return $this->delete($id);
    }

    public function sendPasswordResetEmailAction(Request $request, $id)
    {
       return $this->sendPasswordResetEmail($request, $id);

    }

    public function sendEmailVerifyEmailAction(Request $request, $id)
    {
        return $this->sendEmailVerifyEmail($request, $id);
    }

    public function changeNicknameAction(Request $request, $userId)
    {
        $changeNicknameTwigUrl = 'admin-v2/user/user-manage/staff-manage/change-nickname-modal.html.twig';

        return $this->changeNickname($request, $userId, $changeNicknameTwigUrl);
    }

    public function changePasswordAction(Request $request, $userId)
    {
        $changePasswordTwigUrl = 'admin-v2/user/user-manage/staff-manage/change-password-modal.html.twig';

        return $this->changePassword($request, $userId, $changePasswordTwigUrl);

    }

    public function turnIntoStudentAction($id)
    {
        $user = $this->getUserService()->getUser($id);

        $this->getUserService()->changeUserRoles($user['id'], ['roles' =>'ROLE_USER']);
        $this->getUserService()->updateUser($user['id'],['isStudent' => 1]);

        return $this->createJsonResponse(['success' => true]);

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

}
