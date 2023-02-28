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
    const USER_INFO_FIELDS = [
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
    ];

    public function search(ApiRequest $request)
    {
        $auth = $this->getSettingService()->get('auth');
        $user = $this->getCurrentUser();

        if ($auth['fill_userinfo_after_login'] && empty($auth['registerSort'])) {
            return ['result' => true, 'message' => ''];
        }

        $userInfo = $this->getUserService()->getUserProfile($user['id']);

        $extUserFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
        $extUserFields = ArrayToolkit::index($extUserFields, 'fieldName');

        $userFields = [];
        foreach ($auth['registerSort'] ?? [] as $fieldName) {
            if (!in_array($fieldName, self::USER_INFO_FIELDS)) {
                $checkedField = [
                'fieldName' => $fieldName,
                // todo 敏感信息过滤
                'value' => empty($userInfo[$fieldName]) ? '' : $userInfo[$fieldName],
                'type' => $extUserFields[$fieldName]['type'] ?? $fieldName,
            ];
            }

            if ('select' == $checkedField['type']) {
                $checkedField['detail'] = json_decode($extUserFields[$fieldName]['detail'] ?? '');
            }
            if ('mobile' == $checkedField['type']) {
                $checkedField['value'] = $userFields['verifiedMobile'] ?: '';
                $checkedField['mobileSmsValidate'] = !empty($auth['mobileSmsValidate']);
            }

            $userFields[] = $checkedField;
        }

        return [
            'userFields' => $userFields ?: (object) [],
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
                return ['result' => false, 'message' => $this->trans('register.userinfo_fill_tips')];
            }
        }

        $this->saveUserInfo($formData, $user);

        return ['result' => true, 'message' => ''];
    }

    protected function saveUserInfo($formData, $user)
    {
        // todo 仅更新必要字段
        $userInfo = ArrayToolkit::parts($formData, self::USER_INFO_FIELDS);

        if (isset($formData['email']) && !empty($formData['email'])) {
            $this->getAuthService()->changeEmail($user['id'], null, $formData['email']);
        }

        $authSetting = $this->getSettingService()->get('auth', []);
        if (!empty($formData['mobile']) && !empty($authSetting['fill_userinfo_after_login']) && !empty($authSetting['mobileSmsValidate'])) {
            $verifiedMobile = $formData['mobile'];
            $this->getUserService()->changeMobile($user['id'], $verifiedMobile);
        }

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
