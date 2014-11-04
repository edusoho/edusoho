<?php
namespace Topxia\WebBundle\Controller;

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
                            'form' => $form->createView(),
                            'error' => $error,
                        ));
                    }
                }

                if ($user) {
                    $token = $this->getUserService()->makeToken('password-reset', $user['id'], strtotime('+1 day'));
                    try {
                        $this->sendEmail(
                            $user['email'],
                            "重设{$user['nickname']}在{$this->setting('site.name', 'EDUSOHO')}的密码",
                            $this->renderView('TopxiaWebBundle:PasswordReset:reset.txt.twig', array(
                                'user' => $user,
                                'token' => $token,
                            )), 'html'
                        );
                    } catch (\Exception $e) {
                        $this->getLogService()->error('user', 'password-reset', '重设密码邮件发送失败:' . $e->getMessage());
                        return $this->createMessageResponse('error', '重设密码邮件发送失败，请联系管理员。');
                    }

                    $this->getLogService()->info('user', 'password-reset', "{$user['email']}向发送了找回密码邮件。");

                    return $this->render('TopxiaWebBundle:PasswordReset:sent.html.twig', array(
                        'user' => $user,
                        'emailLoginUrl' => $this->getEmailLoginUrl($user['email']),
                    ));
                } else {
                    $error = '该邮箱地址没有注册过帐号';
                }
            }
        }

        return $this->render("TopxiaWebBundle:PasswordReset:index.html.twig", array(
            'form' => $form->createView(),
            'error' => $error,
        ));
    }

    public function updateAction(Request $request)
    {
        $token = $this->getUserService()->getToken('password-reset', $request->query->get('token'));
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
            'form' => $form->createView(),
        ));
    }

    public function getEmailLoginUrl ($email)
    {
        $host = substr($email, strpos($email, '@') + 1);
        
        if ($host == 'hotmail.com') {
            return 'http://www.' . $host;
        }
        
        if ($host == 'gmail.com') {
            return 'http://mail.google.com';
        }
        
        return 'http://mail.' . $host;
    }

    private function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

}