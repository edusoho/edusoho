<?php
namespace Mooc\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\RegisterController as BaseRegisterController;

class RegisterController extends BaseRegisterController
{
    public function indexAction(Request $request)
    {
        $fields = $request->query->all();
        $user   = $this->getCurrentUser();

        if ($user->isLogin()) {
            return $this->createMessageResponse('info', '你已经登录了', null, 3000, $this->generateUrl('homepage'));
        }

        $registerEnable = $this->getAuthService()->isRegisterEnabled();

        if (!$registerEnable) {
            return $this->createMessageResponse('info', '注册已关闭，请联系管理员', null, 3000, $this->generateUrl('homepage'));
        }

        if ($request->getMethod() == 'POST') {
            $registration = $request->request->all();

// $registration['mobile'] = isset($registration['verifiedMobile']) ? $registration['verifiedMobile'] : '';

            if (isset($registration['emailOrMobile']) && SimpleValidator::mobile($registration['emailOrMobile'])) {
                $registration['verifiedMobile'] = $registration['emailOrMobile'];
            }

            if (!preg_match('/^(?!\d+$)/', $registration['nickname'])) {
                return $this->createMessageResponse('error', '用户名不能全为数字');}

            if ($this->getSensitiveService()->scanText($registration['nickname'])) {
                return $this->createMessageResponse('error', '用户名中含有敏感词！');
            }

            $registration['mobile']    = isset($registration['verifiedMobile']) ? $registration['verifiedMobile'] : '';
            $registration['createdIp'] = $request->getClientIp();
            $authSettings              = $this->getSettingService()->get('auth', array());

            //验证码校验
            $this->captchaEnabledValidator($authSettings, $registration, $request);

//手机校验码

            if ($this->smsCodeValidator($authSettings, $registration, $request)) {
                $registration['verifiedMobile'] = '';
                $request->request->add(array_merge($request->request->all(), array('mobile' => $registration['mobile'])));

                list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, $scenario = 'sms_registration');

                if ($result) {
                    $registration['verifiedMobile'] = $sessionField['to'];
                } else {
                    return $this->createMessageResponse('info', '手机号码和短信验证码不匹配，请重新注册');
                }
            }

//ip次数限制

            if ($this->registerLimitValidator($registration, $authSettings, $request)) {
                return $this->createMessageResponse('info', '由于您注册次数过多，请稍候尝试');
            }

            $user = $this->getAuthService()->register($registration);

            if (($authSettings
                && isset($authSettings['email_enabled'])
                && $authSettings['email_enabled'] == 'closed')
                || !$this->isEmptyVeryfyMobile($user)) {
                $this->authenticateUser($user);
            }

            $goto = $this->generateUrl('register_submited', array(
                'id'   => $user['id'],
                'hash' => $this->makeHash($user),
                'goto' => $this->getTargetPath($request)
            ));

            if ($this->getAuthService()->hasPartnerAuth()) {
                $currentUser = $this->getCurrentUser();

                if (!$currentUser->isLogin()) {
                    $this->authenticateUser($user);
                }

                $goto = $this->generateUrl('partner_login', array('goto' => $goto));
            }

            return $this->redirect($this->generateUrl('register_success', array('goto' => $goto)));
        }

        $inviteCode = '';
        $inviteUser = array();

        if (!empty($fields['inviteCode'])) {
            $inviteUser = $this->getUserService()->getUserByInviteCode($fields['inviteCode']);
        }

        if (!empty($inviteUser)) {
            $inviteCode = $fields['inviteCode'];
        }

        return $this->render("TopxiaWebBundle:Register:index.html.twig", array(
            'inviteCode'        => $inviteCode,
            'isRegisterEnabled' => $registerEnable,
            'registerSort'      => array(),
            'inviteUser'        => $inviteUser,
            '_target_path'      => $this->getTargetPath($request)
        ));
    }

    public function checkStaffNoAction(Request $request)
    {
        $staffNo = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        if ($exclude == $staffNo) {
            return $this->validateResult('success', '');
        }

        $result                 = $this->getAuthService()->checkStaffNo($staffNo);
        list($result, $message) = $result;
        return $this->validateResult($result, $message);
    }
}
