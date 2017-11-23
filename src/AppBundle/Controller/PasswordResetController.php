<?php

namespace AppBundle\Controller;

use Biz\System\Service\LogService;
use Biz\User\Service\AuthService;
use Biz\User\Service\TokenService;
use AppBundle\Common\SmsToolkit;
use Symfony\Component\HttpFoundation\Request;

class PasswordResetController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $data = array('email' => '');

        if ($user->isLogin()) {
            $data['email'] = $user['email'];
        }

        $form = $this->createFormBuilder($data)
            ->add('email', 'email')
            ->getForm();

        $error = null;

        if ('POST' === $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $user = $this->getUserService()->getUserByEmail($data['email']);

                if (empty($user)) {
                    list($result, $message) = $this->getAuthService()->checkEmail($data['email']);

                    if ('error_duplicate' === $result) {
                        $error = '请通过论坛找回密码';

                        return $this->render('password-reset/index.html.twig', array(
                            'form' => $form->createView(),
                            'error' => $error,
                        ));
                    }
                }

                if ($user) {
                    $token = $this->getUserService()->makeToken('password-reset', $user['id'], strtotime('+1 day'));
                    try {
                        $site = $this->setting('site', array());
                        $mailOptions = array(
                            'to' => $user['email'],
                            'template' => 'email_reset_password',
                            'format' => 'html',
                            'params' => array(
                                'nickname' => $user['nickname'],
                                'verifyurl' => $this->generateUrl('password_reset_update', array('token' => $token), true),
                                'sitename' => $site['name'],
                                'siteurl' => $site['url'],
                            ),
                        );

                        $mailFactory = $this->getBiz()->offsetGet('mail_factory');
                        $mail = $mailFactory($mailOptions);
                        $mail->send();
                    } catch (\Exception $e) {
                        $this->getLogService()->error('user', 'password-reset', '重设密码邮件发送失败:'.$e->getMessage());

                        return $this->createMessageResponse('error', '重设密码邮件发送失败，请联系管理员。');
                    }

                    $this->getLogService()->info('user', 'password-reset', "{$user['email']}向发送了找回密码邮件。");

                    return $this->render('password-reset/sent.html.twig', array(
                        'user' => $user,
                        'emailLoginUrl' => $this->getEmailLoginUrl($user['email']),
                    ));
                } else {
                    $error = '该邮箱地址没有注册过帐号';
                }
            }
        }

        return $this->render('password-reset/index.html.twig', array(
            'form' => $form->createView(),
            'error' => $error,
        ));
    }

    public function updateAction(Request $request)
    {
        $token = $this->getUserService()->getToken('password-reset', $request->query->get('token') ?: $request->request->get('token'));

        if (empty($token)) {
            return $this->render('password-reset/error.html.twig');
        }

        $form = $this->createFormBuilder()
            ->add('password', 'password')
            ->add('confirmPassword', 'password')
            ->getForm();

        if ('POST' === $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $this->getAuthService()->changePassword($token['userId'], null, $data['password']);

                $this->getUserService()->deleteToken('password-reset', $token['token']);

                return $this->render('password-reset/success.html.twig');
            }
        }

        return $this->render('password-reset/update.html.twig', array(
            'form' => $form->createView(),
        ));
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

                return $this->redirect($this->generateUrl('password_reset_update', array(
                    'token' => $token,
                )));
            }

            return $this->createMessageResponse('error', '手机短信验证错误，请重新找回');
        }

        return $this->createJsonResponse('GET method');
    }

    public function getEmailLoginUrl($email)
    {
        $host = substr($email, strpos($email, '@') + 1);

        if ('hotmail.com' === $host) {
            return 'http://www.'.$host;
        }

        if ('gmail.com' === $host) {
            return 'http://mail.google.com';
        }

        return 'http://mail.'.$host;
    }

    public function checkMobileExistsAction(Request $request)
    {
        $mobile = $request->query->get('value');
        list($result, $message) = $this->getAuthService()->checkMobile($mobile);

        if ('success' === $result) {
            $response = array('success' => false, 'message' => '该手机号码不存在');
        } else {
            $response = array('success' => true, 'message' => '');
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
