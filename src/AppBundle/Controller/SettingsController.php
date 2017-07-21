<?php

namespace AppBundle\Controller;

use Biz\Content\Service\FileService;
use Biz\Sensitive\Service\SensitiveService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Service\AuthService;
use Biz\User\Service\UserFieldService;
use AppBundle\Common\SmsToolkit;
use AppBundle\Common\CurlToolkit;
use AppBundle\Common\FileToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use AppBundle\Component\OAuthClient\OAuthClientFactory;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class SettingsController extends BaseController
{
    public function profileAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $profile = $this->getUserService()->getUserProfile($user['id']);

        $name = 250;

        $profile['title'] = $user['title'];

        if ($request->getMethod() === 'POST') {
            $profile = $request->request->get('profile');

            if (!((strlen($user['verifiedMobile']) > 0) && (isset($profile['mobile'])))) {
                $this->getUserService()->updateUserProfile($user['id'], $profile);

                $this->setFlashMessage('success', 'site.save.success');
            } else {
                $this->setFlashMessage('danger', 'user.settings.profile.unable_change_bind_mobile');
            }

            return $this->redirect($this->generateUrl('settings'));
        }

        $fields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        if (array_key_exists('idcard', $profile) && $profile['idcard'] == '0') {
            $profile['idcard'] = '';
        }

        $fromCourse = $request->query->get('fromCourse');

        return $this->render('settings/profile.html.twig', array(
            'profile' => $profile,
            'fields' => $fields,
            'fromCourse' => $fromCourse,
            'user' => $user,
        ));
    }

    public function approvalSubmitAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $profile['idcard'] = substr_replace($profile['idcard'], '************', 4, 12);

        if ($request->getMethod() === 'POST') {
            $faceImg = $request->files->get('faceImg');
            $backImg = $request->files->get('backImg');

            if (abs(filesize($faceImg)) > 2 * 1024 * 1024 || abs(filesize($backImg)) > 2 * 1024 * 1024
                || !FileToolkit::isImageFile($backImg) || !FileToolkit::isImageFile($faceImg)) {
                $this->setFlashMessage('danger', 'user.settings.verification.photo_require_tips');

                return $this->render('settings/approval.html.twig', array(
                    'profile' => $profile,
                ));
            }

            $directory = $this->container->getParameter('topxia.upload.private_directory').'/approval';
            $this->getUserService()->applyUserApproval($user['id'], $request->request->all(), $faceImg, $backImg, $directory);

            return $this->redirect($this->generateUrl('setting_approval_submit'));
        }

        return $this->render('settings/approval.html.twig', array(
            'profile' => $profile,
        ));
    }

    public function nicknameAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $isNickname = $this->getSettingService()->get('user_partner');

        if ($isNickname['nickname_enabled'] == 0) {
            return $this->redirect($this->generateUrl('settings'));
        }

        if ($request->getMethod() === 'POST') {
            $nickname = $request->request->get('nickname');

            if ($this->getSensitiveService()->scanText($nickname)) {
                $this->setFlashMessage('danger', 'user.settings.basic_info.illegal_nickname');

                return $this->redirect($this->generateUrl('settings'));
            }

            $this->getAuthService()->changeNickname($user['id'], $nickname);
            $this->setFlashMessage('success', 'user.settings.basic_info.nickname_change_successfully');

            return $this->redirect($this->generateUrl('settings'));
        }

        return $this->render('settings/nickname.html.twig', array(
        ));
    }

    public function nicknameCheckAction(Request $request)
    {
        $nickname = $request->query->get('value');
        $currentUser = $this->getUserService()->getCurrentUser();

        if ($currentUser['nickname'] == $nickname) {
            return $this->createJsonResponse(array('success' => true, 'message' => ''));
        }

        list($result, $message) = $this->getAuthService()->checkUsername($nickname);

        if ($result === 'success') {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => $message);
        }

        return $this->createJsonResponse($response);
    }

    public function avatarAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $form = $this->createFormBuilder()
            ->add('avatar', 'file')
            ->getForm();

        $hasPartnerAuth = $this->getAuthService()->hasPartnerAuth();

        if ($hasPartnerAuth) {
            $partnerAvatar = $this->getAuthService()->getPartnerAvatar($user['id'], 'big');
        } else {
            $partnerAvatar = null;
        }

        $fromCourse = $request->query->get('fromCourse');
        $goto = $request->query->get('goto');

        return $this->render('settings/avatar.html.twig', array(
            'form' => $form->createView(),
            'user' => $this->getUserService()->getUser($user['id']),
            'partnerAvatar' => $partnerAvatar,
            'fromCourse' => $fromCourse,
            'goto' => $goto,
        ));
    }

    public function avatarCropAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if ($request->getMethod() === 'POST') {
            $options = $request->request->all();
            $this->getUserService()->changeAvatar($currentUser['id'], $options['images']);

            return $this->redirect($this->generateUrl('settings_avatar'));
        }

        $fileId = $request->getSession()->get('fileId');
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 270, 270);
        $goto = $request->query->get('goto');

        return $this->render('settings/avatar-crop.html.twig', array(
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
            'goto' => $goto,
        ));
    }

    public function avatarCropModalAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if ($request->getMethod() === 'POST') {
            $options = $request->request->all();
            $this->getUserService()->changeAvatar($currentUser['id'], $options['images']);
            $user = $this->getUserService()->getUser($currentUser['id']);
            $avatar = $this->getWebExtension()->getFpath($user['largeAvatar']);

            return $this->createJsonResponse(array(
                'status' => 'success',
                'avatar' => $avatar, ));
        }

        $fileId = $request->getSession()->get('fileId');
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 270, 270);

        return $this->render('settings/avatar-crop-modal.html.twig', array(
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    public function avatarFetchPartnerAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (!$this->getAuthService()->hasPartnerAuth()) {
            throw $this->createNotFoundException();
        }

        $url = $this->getAuthService()->getPartnerAvatar($currentUser['id'], 'big');

        if (empty($url)) {
            $this->setFlashMessage('danger', 'user.settings.avatar.fetch_form_partner_error');

            return $this->createJsonResponse(true);
        }

        $imgUrl = $request->request->get('imgUrl');
        $file = new File($this->downloadImg($imgUrl));
        $groupCode = 'tmp';
        $imgs = array(
            'large' => array('200', '200'),
            'medium' => array('120', '120'),
            'small' => array('48', '48'),
        );
        $options = array(
            'x' => '0',
            'y' => '0',
            'x2' => '200',
            'y2' => '200',
            'w' => '200',
            'h' => '200',
            'width' => '200',
            'height' => '200',
            'imgs' => $imgs,
        );

        if (empty($options['group'])) {
            $options['group'] = 'default';
        }

        $record = $this->getFileService()->uploadFile($groupCode, $file);
        $parsed = $this->getFileService()->parseFileUri($record['uri']);
        $filePaths = FileToolKit::cropImages($parsed['fullpath'], $options);

        $fields = array();

        foreach ($filePaths as $key => $value) {
            $file = $this->getFileService()->uploadFile($options['group'], new File($value));
            $fields[] = array(
                'type' => $key,
                'id' => $file['id'],
            );
        }

        if (isset($options['deleteOriginFile']) && $options['deleteOriginFile'] == 0) {
            $fields[] = array(
                'type' => 'origin',
                'id' => $record['id'],
            );
        } else {
            $this->getFileService()->deleteFileByUri($record['uri']);
        }

        $this->getUserService()->changeAvatar($currentUser['id'], $fields);

        return $this->createJsonResponse(true);
    }

    public function securityAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user['setup'] || stripos($user['email'], '@eduoho.net') != false) {
            return $this->redirect($this->generateUrl('settings_setup'));
        }

        $hasLoginPassword = strlen($user['password']) > 0;
        $hasPayPassword = strlen($user['payPassword']) > 0;
        $userSecureQuestions = $this->getUserService()->getUserSecureQuestionsByUserId($user['id']);
        $hasFindPayPasswordQuestion = (isset($userSecureQuestions)) && (count($userSecureQuestions) > 0);
        $hasVerifiedMobile = (isset($user['verifiedMobile']) && (strlen($user['verifiedMobile']) > 0));

        $cloudSmsSetting = $this->getSettingService()->get('cloud_sms');
        $showBindMobile = (isset($cloudSmsSetting['sms_enabled'])) && ($cloudSmsSetting['sms_enabled'] == '1')
            && (isset($cloudSmsSetting['sms_bind'])) && ($cloudSmsSetting['sms_bind'] == 'on');

        $itemScore = floor(100.0 / (3.0 + ($showBindMobile ? 1.0 : 0)));
        $progressScore = 1 + ($hasLoginPassword ? $itemScore : 0) + ($hasPayPassword ? $itemScore : 0) + ($hasFindPayPasswordQuestion ? $itemScore : 0) + ($showBindMobile && $hasVerifiedMobile ? $itemScore : 0);

        if ($progressScore <= 1) {
            $progressScore = 0;
        }

        return $this->render('settings/security.html.twig', array(
            'progressScore' => $progressScore,
            'hasLoginPassword' => $hasLoginPassword,
            'hasPayPassword' => $hasPayPassword,
            'hasFindPayPasswordQuestion' => $hasFindPayPasswordQuestion,
            'hasVerifiedMobile' => $hasVerifiedMobile,
        ));
    }

    public function payPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $hasPayPassword = strlen($user['payPassword']) > 0;

        if ($hasPayPassword) {
            return $this->redirect($this->generateUrl('settings_reset_pay_password'));
        }

        $form = $this->createFormBuilder()
            ->add('currentUserLoginPassword', 'password')
            ->add('newPayPassword', 'password')
            ->add('confirmPayPassword', 'password')
            ->getForm();

        if ($user->isLogin() && empty($user['password'])) {
            $request->getSession()->set('_target_path', $this->generateUrl('settings_pay_password'));

            return $this->redirect($this->generateUrl('settings_setup_password'));
        }

        if ($request->getMethod() === 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $passwords = $form->getData();

                if (!$this->getAuthService()->checkPassword($user['id'], $passwords['currentUserLoginPassword'])) {
                    $this->setFlashMessage('danger', 'user.settings.security.pay_password_set.incorrect_login_password');

                    return $this->redirect($this->generateUrl('settings_pay_password'));
                } else {
                    $this->getAuthService()->changePayPassword($user['id'], $passwords['currentUserLoginPassword'], $passwords['newPayPassword']);
                    $this->setFlashMessage('success', 'user.settings.security.pay_password_set.success');
                }

                return $this->redirect($this->generateUrl('settings_reset_pay_password'));
            }
        }

        return $this->render('settings/pay-password.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function setPayPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $hasPayPassword = strlen($user['payPassword']) > 0;

        if ($hasPayPassword) {
            return $this->createJsonResponse('不能直接设置新支付密码。');
        }

        $form = $this->createFormBuilder()
            ->add('currentUserLoginPassword', 'password')
            ->add('newPayPassword', 'password')
            ->add('confirmPayPassword', 'password')
            ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $passwords = $form->getData();

                if (!$this->getAuthService()->checkPassword($user['id'], $passwords['currentUserLoginPassword'])) {
                    return $this->createJsonResponse(array('ACK' => 'fail', 'message' => '当前用户登录密码不正确，请重试！'));
                } else {
                    $this->getAuthService()->changePayPassword($user['id'], $passwords['currentUserLoginPassword'], $passwords['newPayPassword']);

                    return $this->createJsonResponse(array('ACK' => 'success', 'message' => '新支付密码设置成功！'));
                }
            }
        }

        return $this->render('settings/pay-password-modal.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function setPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!empty($user['password'])) {
            throw new \RuntimeException('登录密码已设置，请勿重复设置');
        }

        $form = $this->createFormBuilder()
            ->add('newPassword', 'password')
            ->add('confirmPassword', 'password')
            ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $passwords = $form->getData();
                $this->getUserService()->changePassword($user['id'], $passwords['newPassword']);
                $form = $this->createFormBuilder()
                    ->add('currentUserLoginPassword', 'password')
                    ->add('newPayPassword', 'password')
                    ->add('confirmPayPassword', 'password')
                    ->getForm();

                return $this->render('settings/pay-password-modal.html.twig', array(
                    'form' => $form->createView(),
                ));
            }
        }

        return $this->render('settings/password-modal.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function resetPayPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $form = $this->createFormBuilder()
        // ->add('currentUserLoginPassword','password')
            ->add('oldPayPassword', 'password')
            ->add('newPayPassword', 'password')
            ->add('confirmPayPassword', 'password')
            ->getForm();

        if ($user->isLogin() && empty($user['password'])) {
            $request->getSession()->set('_target_path', $this->generateUrl('settings_reset_pay_password'));

            return $this->redirect($this->generateUrl('settings_setup_password'));
        }

        if ($request->getMethod() === 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $passwords = $form->getData();

                if (!($this->getUserService()->verifyPayPassword($user['id'], $passwords['oldPayPassword']))) {
                    $this->setFlashMessage('danger', 'user.settings.security.pay_password_set.incorrect_pay_password');
                } else {
                    $this->getAuthService()->changePayPasswordWithoutLoginPassword($user['id'], $passwords['newPayPassword']);
                    $this->setFlashMessage('success', 'user.settings.security.pay_password_set.reset_success');
                }

                return $this->redirect($this->generateUrl('settings_reset_pay_password'));
            }
        }

        return $this->render('settings/reset-pay-password.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    protected function setPayPasswordPage($request, $userId)
    {
        $token = $this->getUserService()->makeToken('pay-password-reset', $userId, strtotime('+1 day'));
        $request->request->set('token', $token);

        return $this->forward('AppBundle:Settings:updatePayPassword', array(
            'request' => $request,
        ));
    }

    protected function updatePayPasswordReturn($form, $token)
    {
        return $this->render('settings/update-pay-password-from-email-or-secure-questions.html.twig', array(
            'form' => $form->createView(),
            'token' => $token ?: null,
        ));
    }

    public function updatePayPasswordAction(Request $request)
    {
        $token = $this->getUserService()->getToken('pay-password-reset', $request->query->get('token') ?: $request->request->get('token'));

        if (empty($token)) {
            throw new \RuntimeException('Bad Token!');
        }

        $form = $this->createFormBuilder()
            ->add('payPassword', 'password')
            ->add('confirmPayPassword', 'password')
            ->add('currentUserLoginPassword', 'password')
            ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();

                if ($data['payPassword'] != $data['confirmPayPassword']) {
                    $this->setFlashMessage('danger', 'user.settings.security.pay_password_set.twice_pay_password_mismatch');

                    return $this->updatePayPasswordReturn($form, $token);
                }

                if ($this->getAuthService()->checkPassword($token['userId'], $data['currentUserLoginPassword'])) {
                    $this->getAuthService()->changePayPassword($token['userId'], $data['currentUserLoginPassword'], $data['payPassword']);
                    $this->getUserService()->deleteToken('pay-password-reset', $token['token']);

                    return $this->render('settings/pay-password-success.html.twig');
                } else {
                    $this->setFlashMessage('danger', 'user.settings.security.pay_password_set.incorrect_login_password');
                }
            }
        }

        return $this->updatePayPasswordReturn($form, $token);
    }

    protected function findPayPasswordActionReturn($userSecureQuestions, $hasSecurityQuestions, $hasVerifiedMobile)
    {
        $questionNum = mt_rand(0, 2);
        $question = $userSecureQuestions[$questionNum]['securityQuestionCode'];

        return $this->render('settings/find-pay-password.html.twig', array(
            'question' => $question,
            'questionNum' => $questionNum,
            'hasSecurityQuestions' => $hasSecurityQuestions,
            'hasVerifiedMobile' => $hasVerifiedMobile,
        ));
    }

    public function findPayPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $userSecureQuestions = $this->getUserService()->getUserSecureQuestionsByUserId($user['id']);
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
            $this->setFlashMessage('danger', 'user.settings.security.pay_password_find.empty');

            return $this->forward('AppBundle:Settings:securityQuestions');
        }

        if ($request->getMethod() === 'POST') {
            $questionNum = $request->request->get('questionNum');
            $answer = $request->request->get('answer');

            $userSecureQuestion = $userSecureQuestions[$questionNum];

            $isAnswerRight = $this->getUserService()->verifyInSaltOut(
                $answer, $userSecureQuestion['securityAnswerSalt'], $userSecureQuestion['securityAnswer']);

            if (!$isAnswerRight) {
                $this->setFlashMessage('danger', 'user.settings.security.pay_password_find.wrong_answer');

                return $this->findPayPasswordActionReturn($userSecureQuestions, $hasSecurityQuestions, $hasVerifiedMobile);
            }

            $this->setFlashMessage('success', 'user.settings.security.pay_password_find.correct_answer');

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
            $this->setFlashMessage('danger', 'user.settings.security.pay_password_find.unbind_mobile');

            return $this->redirect($this->generateUrl('settings_bind_mobile', array(
            )));
        }

        if ($request->getMethod() === 'POST') {
            if ($currentUser['verifiedMobile'] != $request->request->get('mobile')) {
                $this->setFlashMessage('danger', 'user.settings.security.pay_password_find.by_mobile.mismatch');
                SmsToolkit::clearSmsSession($request, $scenario);
                goto response;
            }

            list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, $scenario);

            if ($result) {
                $this->setFlashMessage('success', 'user.settings.security.pay_password_find.by_mobile.verify_success');

                return $this->setPayPasswordPage($request, $currentUser['id']);
            }
            $this->setFlashMessage('danger', 'user.settings.security.pay_password_find.by_mobile.verify_fail');
        }

        response:
        return $this->render('settings/find-pay-password-by-sms.html.twig', array(
            'hasSecurityQuestions' => $hasSecurityQuestions,
            'hasVerifiedMobile' => $hasVerifiedMobile,
            'verifiedMobile' => $verifiedMobile,
        ));
    }

    protected function securityQuestionsActionReturn($hasSecurityQuestions, $userSecureQuestions)
    {
        $question1 = null;
        $question2 = null;
        $question3 = null;

        if ($hasSecurityQuestions) {
            $question1 = $userSecureQuestions[0]['securityQuestionCode'];
            $question2 = $userSecureQuestions[1]['securityQuestionCode'];
            $question3 = $userSecureQuestions[2]['securityQuestionCode'];
        }

        return $this->render('settings/security-questions.html.twig', array(
            'hasSecurityQuestions' => $hasSecurityQuestions,
            'question1' => $question1,
            'question2' => $question2,
            'question3' => $question3,
        ));
    }

    public function securityQuestionsAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $userSecureQuestions = $this->getUserService()->getUserSecureQuestionsByUserId($user['id']);
        $hasSecurityQuestions = (isset($userSecureQuestions)) && (count($userSecureQuestions) > 0);

        if ($user->isLogin() && empty($user['password'])) {
            $request->getSession()->set('_target_path', $this->generateUrl('settings_security_questions'));

            return $this->redirect($this->generateUrl('settings_setup_password'));
        }

        if ($request->getMethod() === 'POST') {
            if (!$this->getAuthService()->checkPassword($user['id'], $request->request->get('userLoginPassword'))) {
                $this->setFlashMessage('danger', 'user.settings.security.questions.set.incorrect_password');

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
                'securityQuestion1' => $request->request->get('question-1'),
                'securityAnswer1' => $request->request->get('answer-1'),
                'securityQuestion2' => $request->request->get('question-2'),
                'securityAnswer2' => $request->request->get('answer-2'),
                'securityQuestion3' => $request->request->get('question-3'),
                'securityAnswer3' => $request->request->get('answer-3'),
            );
            $this->getUserService()->addUserSecureQuestionsWithUnHashedAnswers($user['id'], $fields);
            $this->setFlashMessage('success', 'user.settings.security.questions.set.success');
            $hasSecurityQuestions = true;
            $userSecureQuestions = $this->getUserService()->getUserSecureQuestionsByUserId($user['id']);
        }

        return $this->securityQuestionsActionReturn($hasSecurityQuestions, $userSecureQuestions);
    }

    protected function bindMobileReturn($hasVerifiedMobile, $setMobileResult, $verifiedMobile)
    {
        return $this->render('settings/bind-mobile.html.twig', array(
            'hasVerifiedMobile' => $hasVerifiedMobile,
            'setMobileResult' => $setMobileResult,
            'verifiedMobile' => $verifiedMobile,
        ));
    }

    public function bindMobileAction(Request $request)
    {
        $currentUser = $this->getCurrentUser()->toArray();
        $verifiedMobile = '';
        $hasVerifiedMobile = (isset($currentUser['verifiedMobile']) && (strlen($currentUser['verifiedMobile']) > 0));

        if ($hasVerifiedMobile) {
            $verifiedMobile = $currentUser['verifiedMobile'];
        }

        $setMobileResult = 'none';

        $scenario = 'sms_bind';

        if ($this->setting('cloud_sms.sms_enabled') != '1' || $this->setting("cloud_sms.{$scenario}") != 'on') {
            return $this->render('settings/edu-cloud-error.html.twig', array());
        }

        $user = $this->getCurrentUser();

        if ($user->isLogin() && empty($user['password'])) {
            $request->getSession()->set('_target_path', $this->generateUrl('settings_bind_mobile'));

            return $this->redirect($this->generateUrl('settings_setup_password'));
        }

        if ($request->getMethod() === 'POST') {
            $password = $request->request->get('password');

            if (!$this->getAuthService()->checkPassword($currentUser['id'], $password)) {
                $this->setFlashMessage('danger', 'site.incorrect.password');
                SmsToolkit::clearSmsSession($request, $scenario);

                return $this->bindMobileReturn($hasVerifiedMobile, $setMobileResult, $verifiedMobile);
            }

            list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, $scenario);

            if ($result) {
                $verifiedMobile = $sessionField['to'];
                $this->getUserService()->changeMobile($currentUser['id'], $verifiedMobile);

                $setMobileResult = 'success';
                $this->setFlashMessage('success', 'user.settings.security.mobile_bind.success');
            } else {
                $setMobileResult = 'fail';
                $this->setFlashMessage('danger', 'user.settings.security.mobile_bind.fail');
            }
        }

        return $this->bindMobileReturn($hasVerifiedMobile, $setMobileResult, $verifiedMobile);
    }

    public function passwordCheckAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $password = $request->request->get('value');
        $response = array('success' => true);
        if (strlen($password) > 0) {
            $passwordRight = $this->getUserService()->verifyPassword($currentUser['id'], $password);
            if (!$passwordRight) {
                $response = array('success' => false, 'message' => '密码错误');
            }
        } else {
            $response = array('success' => false, 'message' => '密码不能为空');
        }

        return $this->createJsonResponse($response);
    }

    public function passwordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (empty($user['setup'])) {
            return $this->redirect($this->generateUrl('settings_setup'));
        }

        $form = $this->createFormBuilder()
            ->add('currentPassword', 'password')
            ->add('newPassword', 'password')
            ->add('confirmPassword', 'password')
            ->getForm();

        if ($user->isLogin() && empty($user['password'])) {
            $request->getSession()->set('_target_path', $this->generateUrl('settings_security'));

            return $this->redirect($this->generateUrl('settings_setup_password'));
        }

        if ($request->getMethod() === 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $passwords = $form->getData();

                if (!$this->getAuthService()->checkPassword($user['id'], $passwords['currentPassword'])) {
                    $this->setFlashMessage('danger', 'user.settings.security.password_modify.incorrect_password');
                } else {
                    $this->getAuthService()->changePassword($user['id'], $passwords['currentPassword'], $passwords['newPassword']);
                    $this->setFlashMessage('success', 'site.modify.success');
                }

                return $this->redirect($this->generateUrl('settings_password'));
            }
        }

        return $this->render('settings/password.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function emailAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $mailer = $this->getSettingService()->get('mailer', array());
        $cloudEmail = $this->getSettingService()->get('cloud_email_crm', array());

        if (empty($user['setup'])) {
            return $this->redirect($this->generateUrl('settings_setup'));
        }

        $form = $this->createFormBuilder()
            ->add('password', 'password')
            ->add('email', 'text')
            ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $isPasswordOk = $this->getUserService()->verifyPassword($user['id'], $data['password']);

                if (!$isPasswordOk) {
                    $this->setFlashMessage('danger', 'site.incorrect.password');

                    return $this->redirect($this->generateUrl('settings_email'));
                }

                $userOfNewEmail = $this->getUserService()->getUserByEmail($data['email']);

                if ($userOfNewEmail && $userOfNewEmail['id'] == $user['id']) {
                    $this->setFlashMessage('danger', 'user.settings.email.new_email_same_old');

                    return $this->redirect($this->generateUrl('settings_email'));
                }

                if ($userOfNewEmail && $userOfNewEmail['id'] != $user['id']) {
                    $this->setFlashMessage('danger', 'user.settings.email.new_email_not_unique');

                    return $this->redirect($this->generateUrl('settings_email'));
                }

                $tokenArgs = array(
                    'userId' => $user['id'],
                    'duration' => 60 * 60 * 24,
                    'data' => $data['email'],
                );

                $token = $this->getTokenService()->makeToken('email-verify', $tokenArgs);
                $token = $token['token'];
                try {
                    $site = $this->setting('site', array());
                    $mailOptions = array(
                        'to' => $data['email'],
                        'template' => 'email_reset_email',
                        'params' => array(
                            'sitename' => $site['name'],
                            'siteurl' => $site['url'],
                            'verifyurl' => $this->generateUrl('auth_email_confirm', array('token' => $token), true),
                            'nickname' => $user['nickname'],
                        ),
                    );
                    $mailFactory = $this->getBiz()->offsetGet('mail_factory');
                    $mail = $mailFactory($mailOptions);
                    $mail->send();
                    $this->setFlashMessage('success', $this->get('translator')->trans('user.settings.email.send_success', array('%email%' => $data['email'])));
                } catch (\Exception $e) {
                    $this->setFlashMessage('danger', 'user.settings.email.send_error');
                    $this->getLogService()->error('system', 'setting_email_change', '邮箱变更确认邮件发送失败:'.$e->getMessage());
                }

                return $this->redirect($this->generateUrl('settings_email'));
            }
        }

        return $this->render('settings/email.html.twig', array(
            'form' => $form->createView(),
            'mailer' => $mailer,
            'cloudEmail' => $cloudEmail,
        ));
    }

    public function emailVerifyAction()
    {
        $user = $this->getCurrentUser();
        $token = $this->getUserService()->makeToken('email-verify', $user['id'], strtotime('+1 day'), $user['email']);
        $verifyurl = $this->generateUrl('register_email_verify', array('token' => $token), true);
        $site = $this->setting('site', array());
        try {
            $mailOptions = array(
                'to' => $user['email'],
                'template' => 'email_verify_email',
                'params' => array(
                    'verifyurl' => $verifyurl,
                    'nickname' => $user['nickname'],
                    'sitename' => $site['name'],
                    'siteurl' => $site['url'],
                ),
            );
            $mailFactory = $this->getBiz()->offsetGet('mail_factory');
            $mail = $mailFactory($mailOptions);
            $mail->send();
            $this->setFlashMessage('success', $this->get('translator')->trans('user.settings.email.send_success', array('%email%' => $data['email'])));
        } catch (\Exception $e) {
            $this->getLogService()->error('system', 'setting_email-verify', '邮箱验证邮件发送失败:'.$e->getMessage());
            $this->setFlashMessage('danger', 'user.settings.email.send_error');
        }

        return $this->createJsonResponse(true);
    }

    public function bindsAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $clients = OAuthClientFactory::clients();
        $userBinds = $this->getUserService()->findBindsByUserId($user->id) ?: array();

        foreach ($userBinds as $userBind) {
            if ($userBind['type'] === 'weixin') {
                $userBind['type'] = 'weixinweb';
            }

            $clients[$userBind['type']]['status'] = 'bind';
        }

        return $this->render('settings/binds.html.twig', array(
            'clients' => $clients,
        ));
    }

    public function unBindAction(Request $request, $type)
    {
        $user = $this->getCurrentUser();
        $this->checkBindsName($type);
        $userBinds = $this->getUserService()->unBindUserByTypeAndToId($type, $user->id);

        return $this->redirect($this->generateUrl('settings_binds'));
    }

    public function bindAction(Request $request, $type)
    {
        $this->checkBindsName($type);
        $callback = $this->generateUrl('settings_binds_bind_callback', array('type' => $type), true);
        $settings = $this->setting('login_bind');
        $config = array('key' => $settings[$type.'_key'], 'secret' => $settings[$type.'_secret']);
        $client = OAuthClientFactory::create($type, $config);

        return $this->redirect($client->getAuthorizeUrl($callback));
    }

    public function bindCallbackAction(Request $request, $type)
    {
        $this->checkBindsName($type);
        $user = $this->getCurrentUser();

        if (empty($user)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $bind = $this->getUserService()->getUserBindByTypeAndUserId($type, $user->id);

        if (!empty($bind)) {
            $this->setFlashMessage('danger', 'user.settings.security.oauth_bind.duplicate_bind');
            goto response;
        }

        $code = $request->query->get('code');

        if (empty($code)) {
            $this->setFlashMessage('danger', 'user.settings.security.oauth_bind.authentication_fail');
            goto response;
        }

        $callbackUrl = $this->generateUrl('settings_binds_bind_callback', array('type' => $type), true);
        try {
            $token = $this->createOAuthClient($type)->getAccessToken($code, $callbackUrl);
        } catch (\Exception $e) {
            $this->setFlashMessage('danger', 'user.settings.security.oauth_bind.authentication_fail');
            goto response;
        }

        $bind = $this->getUserService()->getUserBindByTypeAndFromId($type, $token['userId']);

        if (!empty($bind)) {
            $this->setFlashMessage('danger', 'user.settings.security.oauth_bind.exist_account');
            goto response;
        }

        $this->getUserService()->bindUser($type, $token['userId'], $user['id'], $token);
        $this->setFlashMessage('success', 'user.settings.security.oauth_bind.success');

        response:
        return $this->redirect($this->generateUrl('settings_binds'));
    }

    public function setupAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ($request->getMethod() === 'POST') {
            $data = $request->request->all();

            $this->getAuthService()->changeEmail($user['id'], null, $data['email']);
            $this->getAuthService()->changeNickname($user['id'], $data['nickname']);
            $user = $this->getUserService()->setupAccount($user['id']);
            $this->authenticateUser($user);

            return $this->createJsonResponse(true);
        }

        return $this->render('settings/setup.html.twig');
    }

    public function setupPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $form = $this->createFormBuilder()
            ->add('newPassword', 'password')
            ->add('confirmPassword', 'password')
            ->getForm();

        if ($request->getMethod() === 'POST') {
            $targetPath = $this->getTargetPath($request);
            $form->bind($request);

            if ($form->isValid()) {
                $passwords = $form->getData();
                $this->getUserService()->changePassword($user['id'], $passwords['newPassword']);

                return $this->redirect($targetPath);
            }
        }

        return $this->render('settings/setup-password.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function setupCheckNicknameAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $nickname = $request->query->get('value');

        if ($nickname == $user['nickname']) {
            $response = array('success' => true);
        } else {
            list($result, $message) = $this->getAuthService()->checkUsername($nickname);

            if ($result === 'success') {
                $response = array('success' => true);
            } else {
                $response = array('success' => false, 'message' => $message);
            }
        }

        return $this->createJsonResponse($response);
    }

    protected function checkBindsName($type)
    {
        $types = array_keys(OAuthClientFactory::clients());

        if (!in_array($type, $types)) {
            throw new NotFoundException('Type Not Found');
        }
    }

    public function fetchAvatar($url)
    {
        return CurlToolkit::request('GET', $url, array(), array('contentType' => 'plain'));
    }

    protected function createOAuthClient($type)
    {
        $settings = $this->setting('login_bind');

        if (empty($settings)) {
            throw new \RuntimeException('第三方登录系统参数尚未配置，请先配置。');
        }

        if (empty($settings) || !isset($settings[$type.'_enabled']) || empty($settings[$type.'_key']) || empty($settings[$type.'_secret'])) {
            throw new \RuntimeException('第三方登录('.$type.')系统参数尚未配置，请先配置。');
        }

        if (!$settings[$type.'_enabled']) {
            throw new \RuntimeException('第三方登录('.$type.')未开启');
        }

        $config = array('key' => $settings[$type.'_key'], 'secret' => $settings[$type.'_secret']);
        $client = OAuthClientFactory::create($type, $config);

        return $client;
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->getBiz()->service('Content:FileService');
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->getBiz()->service('User:AuthService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->getBiz()->service('User:UserFieldService');
    }

    /**
     * @return \Biz\User\Service\TokenService
     */
    protected function getTokenService()
    {
        return $this->getBiz()->service('User:TokenService');
    }

    /**
     * @return SensitiveService
     */
    protected function getSensitiveService()
    {
        return $this->getBiz()->service('Sensitive:SensitiveService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }

    protected function downloadImg($url)
    {
        $currentUser = $this->getCurrentUser();
//        $filename    = md5($url).'_'.time();
        $filePath = $this->container->getParameter('topxia.upload.public_directory').'/tmp/'.$currentUser['id'].'_'.time().'.jpg';

        $fp = fopen($filePath, 'w');
        $img = fopen($url, 'r');
        stream_get_meta_data($img);
        $result = '';
        while (!feof($img)) {
            $result .= fgets($img, 1024);
        }

        fclose($img);
        fwrite($fp, $result);
        fclose($fp);

        return $filePath;
    }
}
