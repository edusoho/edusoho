<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class PasswordResetController extends BaseController
{

    public function indexAction(Request $request,$id)
    {
        $error = null;

        $user = $this->getUserService()->getUser($id);

        if ($user) {
            $token = $this->getUserService()->makeToken('password-reset', $user['id'], strtotime('+1 day'));
            $this->sendEmail(
                $user['email'],
                "{$this->setting('site.name', 'EDUSOHO')} -- 重设密码",
                $this->renderView('TopxiaWebBundle:PasswordReset:reset.txt.twig', array(
                    'user' => $user,
                    'token' => $token,
                    'time' => time(),
                )), 'html'
            );

            return $this->createJsonResponse(array('status' => 'success', 'message' => array('message' =>'邮件发送成功!' )));
        } else {
            $error = '该邮箱地址错误';
        }

        return $this->createJsonResponse(array('status' => 'start', 'message' => array('message' =>'say hello!' )));
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

                $this->getUserService()->changePassword($token['userId'], $data['password']);

                $this->getUserService()->deleteToken('password-reset', $token['token']);

                return $this->render('TopxiaWebBundle:PasswordReset:success.html.twig');

            }
        }

        return $this->render('TopxiaWebBundle:PasswordReset:update.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}