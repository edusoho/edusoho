<?php

namespace ApiBundle\Api\Resource\FillUserInfo;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\SmsToolkit;
use Biz\User\Service\UserFieldService;

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
        if ($user['roles'] != ['ROLE_USER']) {
            return ['result' => true, 'message' => ''];
        }

        if ($auth['fill_userinfo_after_login'] && empty($auth['registerSort'])) {
            return ['result' => true, 'message' => ''];
        }

        $userInfo = $this->getUserService()->getUserProfile($user['id']);

        $extUserFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
        $extUserFields = ArrayToolkit::index($extUserFields, 'fieldName');

        $isFullFill = true;
        $userFields = [];
        $ZhFields = ['truename' => '真实姓名', 'mobile' => '手机号码', 'qq' => 'QQ', 'company' => '公司', 'weixin' => '微信', 'weibo' => '微博', 'idcard' => '身份证号', 'gender' => '性别', 'job' => '职业'];
        foreach ($auth['registerSort'] ?? [] as $fieldName) {
            if (!in_array($fieldName, self::USER_INFO_FIELDS)) {
                continue;
            }

            $checkedField = [
                'fieldName' => $extUserFields[$fieldName]['title'] ?? ($ZhFields[$fieldName] ?? $fieldName),
                'value' => empty($userInfo[$fieldName]) ? '' : $userInfo[$fieldName],
                'type' => $extUserFields[$fieldName]['type'] ?? 'varchar',
                'key' => $fieldName,
                'validate' => $extUserFields[$fieldName]['type'] ?? $fieldName,
            ];

            if ('select' == $checkedField['type']) {
                $checkedField['detail'] = json_decode($extUserFields[$fieldName]['detail'] ?? '[]');
            }

            if ('gender' == $fieldName) {
                $checkedField['type'] = 'select';
                $checkedField['detail'] = ['男', '女', '保密'];
            }

            if ('mobile' == $fieldName) {
                $checkedField['value'] = $this->blurPhoneNumber($userFields['verifiedMobile']) ?: '';
                $checkedField['mobileSmsValidate'] = !empty($auth['mobileSmsValidate']) ? '1' : '0';
            }

            if ('idcard' == $fieldName) {
                $checkedField['value'] = $this->blurIdcardNumber($checkedField['value']);
            }

            if (empty($checkedField['value'])) {
                $isFullFill = false;
            }

            $userFields[] = $checkedField;
        }

        if ($isFullFill) {
            return ['result' => true, 'message' => ''];
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

    public function blurIdcardNumber($idcardNum)
    {
        $head = substr($idcardNum, 0, 4);
        $tail = substr($idcardNum, -2, 2);

        return $head.'************'.$tail;
    }

    public function blurPhoneNumber($phoneNum)
    {
        $head = substr($phoneNum, 0, 3);
        $tail = substr($phoneNum, -4, 4);

        return $head.'****'.$tail;
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
