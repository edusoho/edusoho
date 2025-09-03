<?php

namespace AppBundle\Controller;

use AppBundle\Common\SmsToolkit;
use Biz\System\Service\LogService;
use Biz\User\Service\AuthService;
use Biz\User\Service\TokenService;
use Biz\User\Support\RoleHelper;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;

class PasswordResetController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('password-reset/index.html.twig');
    }

    public function emailResetSuccessAction(Request $request)
    {
        $email = $request->query->get('email');
        $mobile = $request->query->get('mobile');
        if (!empty($email)) {
            $user = $this->getUserService()->getUserByEmail($email);
        } else {
            $user = $this->getUserService()->getUserByVerifiedMobile($mobile);
        }

        return $this->render('password-reset/sent.html.twig', [
            'user' => $user,
            'email' => $email,
            'mobile' => $mobile,
        ]);
    }

    public function updateAction(Request $request)
    {
        $token = $this->getUserService()->getToken('password-reset', $request->query->get('token') ?: $request->request->get('token'));
        if (empty($token)) {
            return $this->render('password-reset/error.html.twig');
        }

        $user = $this->getUserService()->getUser($token['userId']);
        if (empty($user)) {
            return $this->render('password-reset/error.html.twig');
        }

        $form = $this->createFormBuilder(null, [
            'csrf_protection' => false])
            ->add('password', PasswordType::class)
            ->add('confirmPassword', PasswordType::class)
            ->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $this->getUserService()->changePassword($token['userId'], $data['password']);

                $this->getUserService()->deleteToken('password-reset', $token['token']);

                return $this->render('password-reset/success.html.twig');
            }
        }

        return $this->render('password-reset/update.html.twig', [
            'form' => $form->createView(),
            'needStrongPassword' => RoleHelper::isStaff($user['roles']),
        ]);
    }

    public function changeRawPasswordAction(Request $request)
    {
        $fields = $request->query->all();
        $user_token = $this->getTokenService()->verifyToken('email_password_reset', $fields['token']);
        $flag = $this->getUserService()->changeRawPassword($user_token['data']['userId'], $user_token['data']['rawPassword']);

        if (!$flag) {
            return $this->render('password-reset/raw-error.html.twig');
        }

        return $this->render('password-reset/raw-success.html.twig');
    }

    public function resetBySmsAction(Request $request)
    {
        if ('POST' === $request->getMethod()) {
            list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, $scenario = 'sms_forget_password');

            if ($result) {
                $targetUser = $this->getUserService()->getUserByVerifiedMobile($request->request->get('mobile'));

                if (empty($targetUser)) {
                    return $this->createMessageResponse('error', '用户不存在，请重新找回');
                }

                $token = $this->getUserService()->makeToken('password-reset', $targetUser['id'], strtotime('+1 day'));
                $request->request->set('token', $token);

                return $this->redirect($this->generateUrl('password_reset_update', [
                    'token' => $token,
                ]));
            }

            return $this->createMessageResponse('error', '手机短信验证错误，请重新找回');
        }

        return $this->createJsonResponse('GET method');
    }

    public function checkMobileExistsAction(Request $request)
    {
        $mobile = $request->query->get('value');
        list($result, $message) = $this->getAuthService()->checkMobile($mobile);

        if ('success' === $result) {
            $response = ['success' => false, 'message' => '该手机号码不存在'];
        } else {
            $response = ['success' => true, 'message' => ''];
        }

        return $this->createJsonResponse($response);
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->getBiz()->service('User:AuthService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->getBiz()->service('User:TokenService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }
}
