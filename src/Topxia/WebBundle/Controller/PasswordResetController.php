<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\SmsToolkit;
use Topxia\Service\Common\Mail;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\MailFactory;

class PasswordResetController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $data = array('email' => '');

        if ($user->isLogin()) {
            if (!$user['setup'] || stripos($user['email'], '@edusoho.net') != false) {
                return $this->redirect($this->generateUrl('settings_setup'));
            } else {
                $data['email'] = $user['email'];
            }
        }

        $form = $this->createFormBuilder($data)
                     ->add('email', 'email')
                     ->getForm();

        $error = null;

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $user = $this->getUserService()->getUserByEmail($data['email']);

                if (empty($user)) {
                    list($result, $message) = $this->getAuthService()->checkEmail($data['email']);

                    if ($result == 'error_duplicate') {
                        $error = '请通过论坛找回密码';
                        return $this->render("TopxiaWebBundle:PasswordReset:index.html.twig", array(
                            'form'  => $form->createView(),
                            'error' => $error
                        ));
                    }
                }

                if ($user) {
                    $token = $this->getUserService()->makeToken('password-reset', $user['id'], strtotime('+1 day'));
                    try {
                        $site = $this->setting('site', array());
                        $mailOptions = array(
                            'to'     => $user['email'],
                            'template' => 'email_reset_password',
                            'format' => 'html',
                            'params' => array(
                                'nickname' => $user['nickname'],
                                'verifyurl' => $this->generateUrl('password_reset_update', array('token' => $token), true),
                                'sitename' => $site['name'],
                                'siteurl' => $site['url']
                            )
                        );

                        $mail = MailFactory::create($mailOptions);
                        $mail->send();
                    } catch (\Exception $e) {
                        $this->getLogService()->error('user', 'password-reset', '重设密码邮件发送失败:'.$e->getMessage());
                        return $this->createMessageResponse('error', '重设密码邮件发送失败，请联系管理员。');
                    }

                    $this->getLogService()->info('user', 'password-reset', "{$user['email']}向发送了找回密码邮件。");

                    return $this->render('TopxiaWebBundle:PasswordReset:sent.html.twig', array(
                        'user'          => $user,
                        'emailLoginUrl' => $this->getEmailLoginUrl($user['email'])
                    ));
                } else {
                    $error = '该邮箱地址没有注册过帐号';
                }
            }
        }

        return $this->render("TopxiaWebBundle:PasswordReset:index.html.twig", array(
            'form'  => $form->createView(),
            'error' => $error
        ));
    }

    public function updateAction(Request $request)
    {
        $token = $this->getUserService()->getToken('password-reset', $request->query->get('token') ?: $request->request->get('token'));

        if (empty($token)) {
            return $this->render('TopxiaWebBundle:PasswordReset:error.html.twig');
        }

        $form = $this->createFormBuilder()
                     ->add('password', 'password')
                     ->add('confirmPassword', 'password')
                     ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $this->getAuthService()->changePassword($token['userId'], null, $data['password']);

                $this->getUserService()->deleteToken('password-reset', $token['token']);

                return $this->render('TopxiaWebBundle:PasswordReset:success.html.twig');
            }
        }

        return $this->render('TopxiaWebBundle:PasswordReset:update.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function resetBySmsAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, $scenario = 'sms_forget_password');

            if ($result) {
                $targetUser = $this->getUserService()->getUserByVerifiedMobile($request->request->get('mobile'));

                if (empty($targetUser)) {
                    return $this->createMessageResponse('error', '用户不存在，请重新找回');
                }

                $token = $this->getUserService()->makeToken('password-reset', $targetUser['id'], strtotime('+1 day'));
                $request->request->set('token', $token);

                return $this->redirect($this->generateUrl('password_reset_update', array(
                    'token' => $token
                )));
            } else {
                return $this->createMessageResponse('error', '手机短信验证错误，请重新找回');
            }
        }

        return $this->createJsonResponse('GET method');
    }

    public function getEmailLoginUrl($email)
    {
        $host = substr($email, strpos($email, '@') + 1);

        if ($host == 'hotmail.com') {
            return 'http://www.'.$host;
        }

        if ($host == 'gmail.com') {
            return 'http://mail.google.com';
        }

        return 'http://mail.'.$host;
    }

    public function checkMobileExistsAction(Request $request)
    {
        $mobile                 = $request->query->get('value');
        list($result, $message) = $this->getAuthService()->checkMobile($mobile);

        if ($result == 'success') {
            $response = array('success' => false, 'message' => '该手机号码不存在');
        } else {
            $response = array('success' => true, 'message' => '');
        }

        return $this->createJsonResponse($response);
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }
}
