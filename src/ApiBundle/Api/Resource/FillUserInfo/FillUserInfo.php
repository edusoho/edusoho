<?php

namespace ApiBundle\Api\Resource\FillUserInfo;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\UserToolkit;
use Biz\Common\BizSms;
use Biz\Sms\SmsException;
use Biz\User\Service\UserFieldService;

class FillUserInfo extends AbstractResource
{
    const USER_INFO_FIELDS = [
        'email',
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
        $fieldNames = array_intersect($auth['registerSort'] ?? [], $auth['registerFieldNameArray'] ?? []);

        if ($user['roles'] != ['ROLE_USER']) {
            return ['result' => true, 'message' => ''];
        }

        if ($auth['fill_userinfo_after_login'] && empty($fieldNames)) {
            return ['result' => true, 'message' => ''];
        }

        $userInfo = array_merge(
            $this->getUserService()->getUser($user['id']),
            $this->getUserService()->getUserProfile($user['id'])
        );

        $extUserFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
        $extUserFields = ArrayToolkit::index($extUserFields, 'fieldName');

        $isFullFill = true;
        $userFields = [];
        $ZhFields = ['email' => '邮箱', 'truename' => '真实姓名', 'mobile' => '手机号码', 'qq' => 'QQ', 'company' => '公司', 'weixin' => '微信', 'weibo' => '微博', 'idcard' => '身份证号', 'gender' => '性别', 'job' => '职业'];
        foreach ($fieldNames as $fieldName) {
            if (!in_array($fieldName, self::USER_INFO_FIELDS)) {
                continue;
            }

            if ('email' == $fieldName && UserToolkit::isEmailGeneratedBySystem($userInfo[$fieldName])) {
                $userInfo[$fieldName] = '';
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
                $checkedField['detail'] = ['male', 'female', 'secret'];
            }

            if ('email' == $fieldName && !empty($userInfo[$fieldName])) {
                continue;
            }

            if ('mobile' == $fieldName) {
                $checkedField['mobileSmsValidate'] = !empty($auth['mobileSmsValidate']) ? '1' : '0';
                if (!empty($userInfo['verifiedMobile'])) {
                    continue;
                }
            }

            if (in_array($fieldName, ['idcard', 'truename']) && !empty($userInfo[$fieldName]) && in_array($userInfo['approvalStatus'], ['approved', 'approving'])) {
                continue;
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
            $smsToken = $formData['smsToken'] ?? null;
            $mobile = $formData['mobile'];
            $smsCode = $formData['smsCode'] ?? null;
            $status = $this->getBizSms()->check(BizSms::SMS_BIND_TYPE, $mobile, $smsToken, $smsCode);
            if (BizSms::STATUS_SUCCESS !== $status) {
                throw SmsException::FORBIDDEN_SMS_CODE_INVALID();
            }
        }

        $userInfo = $this->saveUserInfo($formData, $user);
        if (!$userInfo) {
            return ['result' => false, 'message' => '字段不能为空'];
        }

        return ['result' => true, 'message' => ''];
    }

    protected function saveUserInfo($formData, $user)
    {
        // todo 仅更新必要字段
        $userInfo = ArrayToolkit::parts($formData, self::USER_INFO_FIELDS);
        foreach ($userInfo as $value) {
            if (empty($value)) {
                return false;
            }
        }
        $authSetting = $this->getSettingService()->get('auth', []);

        if (isset($formData['email']) && !empty($formData['email'])) {
            $this->getAuthService()->changeEmail($user['id'], null, $formData['email']);
        }

        if (!empty($formData['mobile']) && !empty($authSetting['fill_userinfo_after_login']) && !empty($authSetting['mobileSmsValidate'])) {
            $verifiedMobile = $formData['mobile'];
            $this->getUserService()->changeMobile($user['id'], $verifiedMobile);
        }

        $userInfo = $this->getUserService()->updateUserProfile($user['id'], $userInfo);

        return $userInfo;
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

    /**
     * @return BizSms
     */
    private function getBizSms()
    {
        return $this->biz['biz_sms'];
    }
}
