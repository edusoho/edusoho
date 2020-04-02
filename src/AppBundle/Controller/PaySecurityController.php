<?php

namespace AppBundle\Controller;

use AppBundle\Common\SmsToolkit;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;

class PaySecurityController extends BaseController
{
    public function payPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $hasPayPassword = $this->isPayPasswordSetted($user['id']);

        if ($hasPayPassword) {
            $this->setFlashMessage('danger', '不能直接设置新支付密码。');

            return $this->redirect($this->generateUrl('settings_reset_pay_password'));
        }

        $form = $this->createFormBuilder()
            ->add('currentUserLoginPassword', PasswordType::class)
            ->add('newPayPassword', PasswordType::class)
            ->add('confirmPayPassword', PasswordType::class)
            ->getForm();

        if ($user->isLogin() && empty($user['password'])) {
            $request->getSession()->set('_target_path', $this->generateUrl('settings_pay_password'));

            return $this->redirect($this->generateUrl('settings_setup_password'));
        }

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $passwords = $form->getData();

                if ($passwords['confirmPayPassword'] != $passwords['newPayPassword']) {
                    $this->setFlashMessage('danger', '支付密码错误');

                    return $this->redirect($this->generateUrl('settings_pay_password'));
                }

                if (!$this->getAuthService()->checkPassword($user['id'], $passwords['currentUserLoginPassword'])) {
                    $this->setFlashMessage('danger', '当前用户登录密码不正确，请重试！');

                    return $this->redirect($this->generateUrl('settings_pay_password'));
                } else {
                    $this->getAccountService()->setPayPassword($user['id'], $passwords['newPayPassword']);
                    $this->setFlashMessage('success', '新支付密码设置成功，您可以在此重设密码。');
                }

                return $this->redirect($this->generateUrl('settings_reset_pay_password'));
            }
        }

        return $this->render('settings/pay-password.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function setPayPasswordModalAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $hasPayPassword = $this->isPayPasswordSetted($user['id']);

        if ($hasPayPassword) {
            return $this->createJsonResponse('不能直接设置新支付密码。');
        }

        $form = $this->createFormBuilder()
            ->add('currentUserLoginPassword', PasswordType::class)
            ->add('newPayPassword', PasswordType::class)
            ->add('confirmPayPassword', PasswordType::class)
            ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $passwords = $form->getData();

                if (!$this->validateLoginPassword($user['id'], $passwords['currentUserLoginPassword'])) {
                    return $this->createJsonResponse(array('ACK' => 'fail', 'message' => '当前用户登录密码不正确，请重试！'));
                } else {
                    $this->getAccountService()->setPayPassword($user['id'], $passwords['newPayPassword']);

                    return $this->createJsonResponse(array('ACK' => 'success', 'message' => '新支付密码设置成功！'));
                }
            }
        }

        return $this->render('settings/pay-password-modal.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function resetPayPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $form = $this->createFormBuilder()
            ->add('oldPayPassword', PasswordType::class)
            ->add('newPayPassword', PasswordType::class)
            ->add('confirmPayPassword', PasswordType::class)
            ->getForm();

        if ($user->isLogin() && empty($user['password'])) {
            $request->getSession()->set('_target_path', $this->generateUrl('settings_reset_pay_password'));

            return $this->redirect($this->generateUrl('settings_setup_password'));
        }

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $passwords = $form->getData();

                if (!($this->getAccountService()->validatePayPassword($user['id'], $passwords['oldPayPassword']))) {
                    $this->setFlashMessage('danger', '支付密码不正确，请重试！');
                } else {
                    $this->getAccountService()->setPayPassword($user['id'], $passwords['newPayPassword']);
                    $this->setFlashMessage('success', '重置支付密码成功。');
                }

                return $this->redirect($this->generateUrl('settings_reset_pay_password'));
            }
        }

        return $this->render('settings/reset-pay-password.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function updatePayPasswordAction(Request $request)
    {
        $token = $this->getUserService()->getToken('pay-password-reset', $request->query->get('token') ?: $request->request->get('token'));

        if (empty($token)) {
            throw new \RuntimeException('Bad Token!');
        }

        $form = $this->createFormBuilder()
            ->add('payPassword', PasswordType::class)
            ->add('confirmPayPassword', PasswordType::class)
            ->add('currentUserLoginPassword', PasswordType::class)
            ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                if ($data['payPassword'] != $data['confirmPayPassword']) {
                    $this->setFlashMessage('danger', '两次输入的支付密码不一致。');

                    return $this->updatePayPasswordReturn($form, $token);
                }

                if ($this->getAuthService()->checkPassword($token['userId'], $data['currentUserLoginPassword'])) {
                    $this->getAccountService()->setPayPassword($token['userId'], $data['payPassword']);
                    $this->getUserService()->deleteToken('pay-password-reset', $token['token']);

                    return $this->render('settings/pay-password-success.html.twig');
                } else {
                    $this->setFlashMessage('danger', '用户登录密码错误。');
                }
            }
        }

        return $this->updatePayPasswordReturn($form, $token);
    }

    public function findPayPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $userSecureQuestions = $this->getAccountService()->findSecurityAnswersByUserId($user['id']);
        $hasSecurityQuestions = null !== $userSecureQuestions && count($userSecureQuestions) > 0;
        $verifiedMobile = $user['verifiedMobile'];
        $hasVerifiedMobile = null !== $verifiedMobile && strlen($verifiedMobile) > 0;
        $canSmsFind = ($hasVerifiedMobile) &&
            ($this->setting('cloud_sms.sms_enabled') == '1') &&
            ($this->setting('cloud_sms.sms_forget_pay_password') == 'on');

        if ((!$hasSecurityQuestions) && ($canSmsFind)) {
            return $this->redirect($this->generateUrl('settings_find_pay_password_by_sms', array()));
        }

        if (!$hasSecurityQuestions) {
            $this->setFlashMessage('danger', '您还没有安全问题，请先设置。');

            return $this->forward('AppBundle:Settings:securityQuestions');
        }

        if ($request->getMethod() === 'POST') {
            $questionKey = $request->request->get('question');
            $answer = $request->request->get('answer');

            $isAnswerRight = $this->getAccountService()->validateSecurityAnswer($user['id'], $questionKey,
                $answer);

            if (!$isAnswerRight) {
                $this->setFlashMessage('danger', '回答错误。');

                return $this->findPayPasswordActionReturn($userSecureQuestions, $hasSecurityQuestions, $hasVerifiedMobile);
            }

            $this->setFlashMessage('success', '回答正确，你可以开始更新支付密码。');

            return $this->setPayPasswordPage($request, $user['id']);
        }

        return $this->findPayPasswordActionReturn($userSecureQuestions, $hasSecurityQuestions, $hasVerifiedMobile);
    }

    public function findPayPasswordBySmsAction(Request $request)
    {
        $scenario = 'sms_forget_pay_password';

        if ($this->setting('cloud_sms.sms_enabled') != '1' || $this->setting("cloud_sms.{$scenario}") !== 'on') {
            return $this->render('settings/edu-cloud-error.html.twig', array());
        }

        $currentUser = $this->getCurrentUser();

        $userSecureQuestions = $this->getUserService()->getUserSecureQuestionsByUserId($currentUser['id']);
        $hasSecurityQuestions = null !== $userSecureQuestions && count($userSecureQuestions) > 0;
        $verifiedMobile = $currentUser['verifiedMobile'];
        $hasVerifiedMobile = null !== $verifiedMobile && strlen($verifiedMobile) > 0;

        if (!$hasVerifiedMobile) {
            $this->setFlashMessage('danger', '您还没有绑定手机，请先绑定。');

            return $this->redirect($this->generateUrl('settings_bind_mobile', array(
            )));
        }

        if ($request->getMethod() === 'POST') {
            if ($currentUser['verifiedMobile'] != $request->request->get('mobile')) {
                $this->setFlashMessage('danger', '您输入的手机号，不是已绑定的手机');
                SmsToolkit::clearSmsSession($request, $scenario);
                goto response;
            }

            list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, $scenario);

            if ($result) {
                $this->setFlashMessage('success', '验证通过，你可以开始更新支付密码。');

                return $this->setPayPasswordPage($request, $currentUser['id']);
            }
            $this->setFlashMessage('danger', '验证错误。');
        }

        response:
        return $this->render('settings/find-pay-password-by-sms.html.twig', array(
            'hasSecurityQuestions' => $hasSecurityQuestions,
            'hasVerifiedMobile' => $hasVerifiedMobile,
            'verifiedMobile' => $verifiedMobile,
        ));
    }

    public function securityQuestionsAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $userSecureQuestions = $this->getAccountService()->findSecurityAnswersByUserId($user['id']);
        $hasSecurityQuestions = (isset($userSecureQuestions)) && (count($userSecureQuestions) > 0);

        if ($user->isLogin() && empty($user['password'])) {
            $request->getSession()->set('_target_path', $this->generateUrl('settings_security_questions'));

            return $this->redirect($this->generateUrl('settings_setup_password'));
        }

        if ($request->getMethod() === 'POST') {
            if (!$this->getAuthService()->checkPassword($user['id'], $request->request->get('userLoginPassword'))) {
                $this->setFlashMessage('danger', '您的登录密码错误，不能设置安全问题。');

                return $this->securityQuestionsActionReturn($hasSecurityQuestions, $userSecureQuestions);
            }

            if ($hasSecurityQuestions) {
                throw new \RuntimeException('您已经设置过安全问题，不可再次修改。');
            }

            if ($request->request->get('question-1') == $request->request->get('question-2')
                || $request->request->get('question-1') == $request->request->get('question-3')
                || $request->request->get('question-2') == $request->request->get('question-3')) {
                throw new \RuntimeException('2个问题不能一样。');
            }

            $fields = array(
                $request->request->get('question-1') => $request->request->get('answer-1'),
                $request->request->get('question-2') => $request->request->get('answer-2'),
                $request->request->get('question-3') => $request->request->get('answer-3'),
            );
            $this->getAccountService()->setSecurityAnswers($user['id'], $fields);
            $this->setFlashMessage('success', '安全问题设置成功。');
            $hasSecurityQuestions = true;
            $userSecureQuestions = $this->getUserService()->getUserSecureQuestionsByUserId($user['id']);
        }

        return $this->securityQuestionsActionReturn($hasSecurityQuestions, $userSecureQuestions);
    }

    protected function findPayPasswordActionReturn($userSecureQuestions, $hasSecurityQuestions, $hasVerifiedMobile)
    {
        $questionNum = mt_rand(0, 2);
        $question = $userSecureQuestions[$questionNum]['question_key'];

        return $this->render('settings/find-pay-password.html.twig', array(
            'question' => $question,
            'hasSecurityQuestions' => $hasSecurityQuestions,
            'hasVerifiedMobile' => $hasVerifiedMobile,
        ));
    }

    protected function securityQuestionsActionReturn($hasSecurityQuestions, $userSecureQuestions)
    {
        $question1 = null;
        $question2 = null;
        $question3 = null;

        if ($hasSecurityQuestions) {
            $question1 = $userSecureQuestions[0]['question_key'];
            $question2 = $userSecureQuestions[1]['question_key'];
            $question3 = $userSecureQuestions[2]['question_key'];
        }

        return $this->render('settings/security-questions.html.twig', array(
            'hasSecurityQuestions' => $hasSecurityQuestions,
            'question1' => $question1,
            'question2' => $question2,
            'question3' => $question3,
        ));
    }

    protected function setPayPasswordPage($request, $userId)
    {
        $token = $this->getUserService()->makeToken('pay-password-reset', $userId, strtotime('+1 day'));
        $request->request->set('token', $token);

        return $this->forward('AppBundle:PaySecurity:updatePayPassword', array(
            'request' => $request,
        ));
    }

    protected function validateLoginPassword($userId, $password)
    {
        return $this->getAuthService()->checkPassword($userId, $password);
    }

    protected function updatePayPasswordReturn($form, $token)
    {
        return $this->render('settings/update-pay-password-from-email-or-secure-questions.html.twig', array(
            'form' => $form->createView(),
            'token' => $token ?: null,
        ));
    }

    protected function getAuthService()
    {
        return $this->getBiz()->service('User:AuthService');
    }

    protected function isPayPasswordSetted($userId)
    {
        return $this->getAccountService()->isPayPasswordSetted($userId);
    }

    protected function getAccountService()
    {
        return $this->getBiz()->service('Pay:AccountService');
    }
}
