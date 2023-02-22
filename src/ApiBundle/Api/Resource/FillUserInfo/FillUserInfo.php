<?php


namespace ApiBundle\Api\Resource\FillUserInfo;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\SmsToolkit;
use Biz\User\CurrentUser;
use Biz\User\Service\UserFieldService;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class FillUserInfo extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $auth = $this->getSettingService()->get('auth');
        $user = $this->getCurrentUser();

        if ($auth['fill_userinfo_after_login'] && !isset($auth['registerSort'])) {
            return ['result' => true, 'message' => ''];
        }

        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
        $userFields = ArrayToolkit::index($userFields, 'fieldName');
        $userInfo = $this->getUserService()->getUserProfile($user['id']);

        return [
            'userFields' => $userFields,
            'user' => $userInfo,
            'showNavTip' => 0,
        ];
    }

    public function add(ApiRequest $request)
    {
        $formData = $request->request->all();
        $authSetting = $this->getSettingService()->get('auth', []);
        $user = $this->getCurrentUser();

        if (!empty($formData['mobile']) && !empty($authSetting['mobileSmsValidate'])) {
            list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, 'sms_bind');

            if (!$result) {
                return ['result' => true, 'message' => ''];
            }
        }

        $userInfo = $this->saveUserInfo($request, $user);

        return ['result' => true, 'message' => ''];
    }

    protected function saveUserInfo($request, $user)
    {
        $formData = $request->request->all();
        $userInfo = ArrayToolkit::parts($formData, [
            'truename',
            'mobile',
            'qq',
            'company',
            'weixin',
            'weibo',
            'idcard',
            'gender',
            'job',
            'intField1', 'intField2', 'intField3', 'intField4', 'intField5',
            'floatField1', 'floatField2', 'floatField3', 'floatField4', 'floatField5',
            'dateField1', 'dateField2', 'dateField3', 'dateField4', 'dateField5',
            'varcharField1', 'varcharField2', 'varcharField3', 'varcharField4', 'varcharField5', 'varcharField10', 'varcharField6', 'varcharField7', 'varcharField8', 'varcharField9',
            'textField1', 'textField2', 'textField3', 'textField4', 'textField5', 'textField6', 'textField7', 'textField8', 'textField9', 'textField10',
            'selectField1', 'selectField2', 'selectField3', 'selectField4', 'selectField5',
        ]);

        if (isset($formData['email']) && !empty($formData['email'])) {
            $this->getAuthService()->changeEmail($user['id'], null, $formData['email']);
        }

        $authSetting = $this->getSettingService()->get('auth', []);
        if (!empty($formData['mobile']) && !empty($authSetting['fill_userinfo_after_login']) && !empty($authSetting['mobileSmsValidate'])) {
            $verifiedMobile = $formData['mobile'];
            $this->getUserService()->changeMobile($user['id'], $verifiedMobile);
        }

        $currentUser = new CurrentUser();
        $currentUser->fromArray($this->getUserService()->getUser($user['id']));
        $this->switchUser($request, $currentUser);

        $userInfo = $this->getUserService()->updateUserProfile($user['id'], $userInfo);

        return $userInfo;
    }


    /**
     * switch current user.
     *
     * @return CurrentUser
     */
    protected function switchUser($request, CurrentUser $user)
    {
        $user['currentIp'] = $request->getHttpRequest()->getClientIp();

        $token = new UsernamePasswordToken($user, null, 'main', $user['roles']);
        $this->container->get('security.token_storage')->setToken($token);

        $biz = $this->getBiz();
        $biz['user'] = $user;

        return $user;
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->service('User:UserFieldService');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }


    protected function getAuthService()
    {
        return $this->service('User:AuthService');
    }
}