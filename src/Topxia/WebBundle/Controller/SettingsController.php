<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\SmsToolkit;
use Topxia\Common\CurlToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Service\Common\MailFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\Component\OAuthClient\OAuthClientFactory;

class SettingsController extends BaseController
{
    public function profileAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $profile = $this->getUserService()->getUserProfile($user['id']);

        $profile['title'] = $user['title'];

        if ($request->getMethod() == 'POST') {
            $profile = $request->request->get('profile');

            if (!((strlen($user['verifiedMobile']) > 0) && (isset($profile['mobile'])))) {
                $this->getUserService()->updateUserProfile($user['id'], $profile);
                $this->setFlashMessage('success', '基础信息保存成功。');
            } else {
                $this->setFlashMessage('danger', '不能修改已绑定的手机。');
            }

            return $this->redirect($this->generateUrl('settings'));
        }

        $fields = $this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();

        if (array_key_exists('idcard', $profile) && $profile['idcard'] == "0") {
            $profile['idcard'] = "";
        }

        $fromCourse = $request->query->get('fromCourse');
        return $this->render('TopxiaWebBundle:Settings:profile.html.twig', array(
            'profile'    => $profile,
            'fields'     => $fields,
            'fromCourse' => $fromCourse,
            'user'       => $user
        ));
    }

    public function approvalSubmitAction(Request $request)
    {
        $user              = $this->getCurrentUser();
        $profile           = $this->getUserService()->getUserProfile($user['id']);
        $profile['idcard'] = substr_replace($profile['idcard'], '************', 4, 12);

        if ($request->getMethod() == 'POST') {
            $faceImg = $request->files->get('faceImg');
            $backImg = $request->files->get('backImg');

            if (abs(filesize($faceImg)) > 2 * 1024 * 1024 || abs(filesize($backImg)) > 2 * 1024 * 1024) {
                $this->setFlashMessage('danger', '上传文件过大，请上传较小的文件!');
                return $this->render('TopxiaWebBundle:Settings:approval.html.twig', array(
                    'profile' => $profile
                ));
            }

            if (!FileToolkit::isImageFile($backImg) || !FileToolkit::isImageFile($faceImg)) {
                // return $this->createMessageResponse('error', '上传图片格式错误，请上传jpg, bmp,gif, png格式的文件。');
                $this->setFlashMessage('danger', '上传图片格式错误，请上传jpg, bmp,gif, png格式的文件。');
                return $this->render('TopxiaWebBundle:Settings:approval.html.twig', array(
                    'profile' => $profile
                ));
            }

            $directory = $this->container->getParameter('topxia.upload.private_directory').'/approval';
            $this->getUserService()->applyUserApproval($user['id'], $request->request->all(), $faceImg, $backImg, $directory);
            // $this->setFlashMessage('success', '实名认证提交成功！');
            return $this->redirect($this->generateUrl('setting_approval_submit'));
        }

        return $this->render('TopxiaWebBundle:Settings:approval.html.twig', array(
            'profile' => $profile
        ));
    }

    public function nicknameAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $isNickname = $this->getSettingService()->get('user_partner');

        if ($isNickname['nickname_enabled'] == 0) {
            return $this->redirect($this->generateUrl('settings'));
        }

        if ($request->getMethod() == 'POST') {
            $nickname = $request->request->get('nickname');

            if ($this->getSensitiveService()->scanText($nickname)) {
                $this->setFlashMessage('danger', '用户名中含有敏感词，更新失败！');
                return $this->redirect($this->generateUrl('settings'));
            }

            $this->getAuthService()->changeNickname($user['id'], $nickname);
            $this->setFlashMessage('success', '用户名修改成功！');
            return $this->redirect($this->generateUrl('settings'));
        }

        return $this->render('TopxiaWebBundle:Settings:nickname.html.twig', array(
        ));
    }

    public function nicknameCheckAction(Request $request)
    {
        $nickname    = $request->query->get('value');
        $currentUser = $this->getUserService()->getCurrentUser();

        if ($currentUser['nickname'] == $nickname) {
            return $this->createJsonResponse(array('success' => true, 'message' => ''));
        }

        list($result, $message) = $this->getAuthService()->checkUsername($nickname);

        if ($result == 'success') {
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
        $goto       = $request->query->get('goto');
        return $this->render('TopxiaWebBundle:Settings:avatar.html.twig', array(
            'form'          => $form->createView(),
            'user'          => $this->getUserService()->getUser($user['id']),
            'partnerAvatar' => $partnerAvatar,
            'fromCourse'    => $fromCourse,
            'goto'          => $goto
        ));
    }

    public function avatarCropAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if ($request->getMethod() == 'POST') {
            $options = $request->request->all();
            $this->getUserService()->changeAvatar($currentUser['id'], $options["images"]);
            return $this->redirect($this->generateUrl('settings_avatar'));
        }

        $fileId                                      = $request->getSession()->get("fileId");
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 270, 270);
        $goto                                        = $request->query->get('goto');
        return $this->render('TopxiaWebBundle:Settings:avatar-crop.html.twig', array(
            'pictureUrl'  => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize'  => $scaledSize,
            'goto'        => $goto

        ));
    }

    public function avatarCropModalAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if ($request->getMethod() == 'POST') {
            $options = $request->request->all();
            $this->getUserService()->changeAvatar($currentUser['id'], $options["images"]);
            $user   = $this->getUserService()->getUser($currentUser['id']);
            $avatar = $this->getWebExtension()->getFpath($user['largeAvatar']);
            return $this->createJsonResponse(array(
                'status' => 'success',
                'avatar' => $avatar));
        }

        $fileId                                      = $request->getSession()->get("fileId");
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 270, 270);
        return $this->render('TopxiaWebBundle:Settings:avatar-crop-modal.html.twig', array(
            'pictureUrl'  => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize'  => $scaledSize
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
            $this->setFlashMessage('danger', '获取论坛头像地址失败！');
            return $this->createJsonResponse(true);
        }

        $imgUrl    = $request->request->get('imgUrl');
        $file      = new File($this->downloadImg($imgUrl));
        $groupCode = "tmp";
        $imgs      = array(
            'large'  => array("200", "200"),
            'medium' => array("120", "120"),
            'small'  => array("48", "48")
        );
        $options = array(
            'x'      => "0",
            'y'      => "0",
            'x2'     => "200",
            'y2'     => "200",
            'w'      => "200",
            'h'      => "200",
            'width'  => "200",
            'height' => "200",
            'imgs'   => $imgs
        );

        if (empty($options['group'])) {
            $options['group'] = "default";
        }

        $record    = $this->getFileService()->uploadFile($groupCode, $file);
        $parsed    = $this->getFileService()->parseFileUri($record['uri']);
        $filePaths = FileToolKit::cropImages($parsed["fullpath"], $options);

        $fields = array();

        foreach ($filePaths as $key => $value) {
            $file     = $this->getFileService()->uploadFile($options["group"], new File($value));
            $fields[] = array(
                "type" => $key,
                "id"   => $file['id']
            );
        }

        if (isset($options["deleteOriginFile"]) && $options["deleteOriginFile"] == 0) {
            $fields[] = array(
                "type" => "origin",
                "id"   => $record['id']
            );
        } else {
            $this->getFileService()->deleteFileByUri($record["uri"]);
        }

        $this->getUserService()->changeAvatar($currentUser["id"], $fields);

        return $this->createJsonResponse(true);
    }

    public function securityAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user['setup'] || stripos($user['email'], '@eduoho.net') != false) {
            return $this->redirect($this->generateUrl('settings_setup'));
        }

        $hasLoginPassword           = strlen($user['password']) > 0;
        $hasPayPassword             = strlen($user['payPassword']) > 0;
        $userSecureQuestions        = $this->getUserService()->getUserSecureQuestionsByUserId($user['id']);
        $hasFindPayPasswordQuestion = (isset($userSecureQuestions)) && (count($userSecureQuestions) > 0);
        $hasVerifiedMobile          = (isset($user['verifiedMobile']) && (strlen($user['verifiedMobile']) > 0));

        $cloudSmsSetting = $this->getSettingService()->get('cloud_sms');
        $showBindMobile  = (isset($cloudSmsSetting['sms_enabled'])) && ($cloudSmsSetting['sms_enabled'] == '1')
            && (isset($cloudSmsSetting['sms_bind'])) && ($cloudSmsSetting['sms_bind'] == 'on');

        $itemScore     = floor(100.0 / (3.0 + ($showBindMobile ? 1.0 : 0)));
        $progressScore = 1 + ($hasLoginPassword ? $itemScore : 0) + ($hasPayPassword ? $itemScore : 0) + ($hasFindPayPasswordQuestion ? $itemScore : 0) + ($showBindMobile && $hasVerifiedMobile ? $itemScore : 0);

        if ($progressScore <= 1) {
            $progressScore = 0;
        }

        return $this->render('TopxiaWebBundle:Settings:security.html.twig', array(
            'progressScore'              => $progressScore,
            'hasLoginPassword'           => $hasLoginPassword,
            'hasPayPassword'             => $hasPayPassword,
            'hasFindPayPasswordQuestion' => $hasFindPayPasswordQuestion,
            'hasVerifiedMobile'          => $hasVerifiedMobile
        ));
    }

    public function payPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $hasPayPassword = strlen($user['payPassword']) > 0;

        if ($hasPayPassword) {
            $this->setFlashMessage('danger', '不能直接设置新支付密码。');
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

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $passwords = $form->getData();

                if (!$this->getAuthService()->checkPassword($user['id'], $passwords['currentUserLoginPassword'])) {
                    $this->setFlashMessage('danger', '当前用户登录密码不正确，请重试！');
                    return $this->redirect($this->generateUrl('settings_pay_password'));
                } else {
                    $this->getAuthService()->changePayPassword($user['id'], $passwords['currentUserLoginPassword'], $passwords['newPayPassword']);
                    $this->setFlashMessage('success', '新支付密码设置成功，您可以在此重设密码。');
                }

                return $this->redirect($this->generateUrl('settings_reset_pay_password'));
            }
        }

        return $this->render('TopxiaWebBundle:Settings:pay-password.html.twig', array(
            'form' => $form->createView()
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

        if ($request->getMethod() == 'POST') {
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

        return $this->render('TopxiaWebBundle:Settings:pay-password-modal.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function setPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!empty($user['password'])) {
            throw new \RuntimeException("登录密码已设置，请勿重复设置");
        }

        $form = $this->createFormBuilder()
            ->add('newPassword', 'password')
            ->add('confirmPassword', 'password')
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $passwords = $form->getData();
                $this->getUserService()->changePassword($user['id'], $passwords['newPassword']);
                $form = $this->createFormBuilder()
                    ->add('currentUserLoginPassword', 'password')
                    ->add('newPayPassword', 'password')
                    ->add('confirmPayPassword', 'password')
                    ->getForm();

                return $this->render('TopxiaWebBundle:Settings:pay-password-modal.html.twig', array(
                    'form' => $form->createView()
                ));
            }
        }

        return $this->render('TopxiaWebBundle:Settings:password-modal.html.twig', array(
            'form' => $form->createView()
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

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $passwords = $form->getData();

                if (!($this->getUserService()->verifyPayPassword($user['id'], $passwords['oldPayPassword']))) {
                    $this->setFlashMessage('danger', '支付密码不正确，请重试！');
                } else {
                    $this->getAuthService()->changePayPasswordWithoutLoginPassword($user['id'], $passwords['newPayPassword']);
                    $this->setFlashMessage('success', '重置支付密码成功。');
                }

                return $this->redirect($this->generateUrl('settings_reset_pay_password'));
            }
        }

        return $this->render('TopxiaWebBundle:Settings:reset-pay-password.html.twig', array(
            'form' => $form->createView()
        ));
    }

    protected function setPayPasswordPage($request, $userId)
    {
        $token = $this->getUserService()->makeToken('pay-password-reset', $userId, strtotime('+1 day'));
        $request->request->set('token', $token);
        return $this->forward('TopxiaWebBundle:Settings:updatePayPassword', array(
            'request' => $request
        ));
    }

    protected function updatePayPasswordReturn($form, $token)
    {
        return $this->render('TopxiaWebBundle:Settings:update-pay-password-from-email-or-secure-questions.html.twig', array(
            'form'  => $form->createView(),
            'token' => $token ?: null
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

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();

                if ($data['payPassword'] != $data['confirmPayPassword']) {
                    $this->setFlashMessage('danger', '两次输入的支付密码不一致。');
                    return $this->updatePayPasswordReturn($form, $token);
                }

                if ($this->getAuthService()->checkPassword($token['userId'], $data['currentUserLoginPassword'])) {
                    $this->getAuthService()->changePayPassword($token['userId'], $data['currentUserLoginPassword'], $data['payPassword']);
                    $this->getUserService()->deleteToken('pay-password-reset', $token['token']);
                    return $this->render('TopxiaWebBundle:Settings:pay-password-success.html.twig');
                } else {
                    $this->setFlashMessage('danger', '用户登录密码错误。');
                }
            }
        }

        return $this->updatePayPasswordReturn($form, $token);
    }

    protected function findPayPasswordActionReturn($userSecureQuestions, $hasSecurityQuestions, $hasVerifiedMobile)
    {
        $questionNum = rand(0, 2);
        $question    = $userSecureQuestions[$questionNum]['securityQuestionCode'];
        return $this->render('TopxiaWebBundle:Settings:find-pay-password.html.twig', array(
            'question'             => $question,
            'questionNum'          => $questionNum,
            'hasSecurityQuestions' => $hasSecurityQuestions,
            'hasVerifiedMobile'    => $hasVerifiedMobile
        ));
    }

    public function findPayPasswordAction(Request $request)
    {
        $user                 = $this->getCurrentUser();
        $userSecureQuestions  = $this->getUserService()->getUserSecureQuestionsByUserId($user['id']);
        $hasSecurityQuestions = (isset($userSecureQuestions)) && (count($userSecureQuestions) > 0);
        $verifiedMobile       = $user['verifiedMobile'];
        $hasVerifiedMobile    = (isset($verifiedMobile)) && (strlen($verifiedMobile) > 0);
        $canSmsFind           = ($hasVerifiedMobile) &&
            ($this->setting('cloud_sms.sms_enabled') == '1') &&
            ($this->setting('cloud_sms.sms_forget_pay_password') == 'on');

        if ((!$hasSecurityQuestions) && ($canSmsFind)) {
            return $this->redirect($this->generateUrl('settings_find_pay_password_by_sms', array()));
        }

        if (!$hasSecurityQuestions) {
            $this->setFlashMessage('danger', '您还没有安全问题，请先设置。');
            return $this->forward('TopxiaWebBundle:Settings:securityQuestions');
        }

        if ($request->getMethod() == 'POST') {
            $questionNum = $request->request->get('questionNum');
            $answer      = $request->request->get('answer');

            $userSecureQuestion = $userSecureQuestions[$questionNum];

            $isAnswerRight = $this->getUserService()->verifyInSaltOut(
                $answer, $userSecureQuestion['securityAnswerSalt'], $userSecureQuestion['securityAnswer']);

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
        $scenario = "sms_forget_pay_password";

        if ($this->setting('cloud_sms.sms_enabled') != '1' || $this->setting("cloud_sms.{$scenario}") != 'on') {
            return $this->render('TopxiaWebBundle:Settings:edu-cloud-error.html.twig', array());
        }

        $currentUser = $this->getCurrentUser();

        $userSecureQuestions  = $this->getUserService()->getUserSecureQuestionsByUserId($currentUser['id']);
        $hasSecurityQuestions = (isset($userSecureQuestions)) && (count($userSecureQuestions) > 0);
        $verifiedMobile       = $currentUser['verifiedMobile'];
        $hasVerifiedMobile    = (isset($verifiedMobile)) && (strlen($verifiedMobile) > 0);

        if (!$hasVerifiedMobile) {
            $this->setFlashMessage('danger', '您还没有绑定手机，请先绑定。');
            return $this->redirect($this->generateUrl('settings_bind_mobile', array(
            )));
        }

        if ($request->getMethod() == 'POST') {
            if ($currentUser['verifiedMobile'] != $request->request->get('mobile')) {
                $this->setFlashMessage('danger', '您输入的手机号，不是已绑定的手机');
                SmsToolkit::clearSmsSession($request, $scenario);
                goto response;
            }

            list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, $scenario);

            if ($result) {
                $this->setFlashMessage('success', '验证通过，你可以开始更新支付密码。');
                return $this->setPayPasswordPage($request, $currentUser['id']);
            } else {
                $this->setFlashMessage('danger', '验证错误。');
            }
        }

        response:
        return $this->render('TopxiaWebBundle:Settings:find-pay-password-by-sms.html.twig', array(
            'hasSecurityQuestions' => $hasSecurityQuestions,
            'hasVerifiedMobile'    => $hasVerifiedMobile,
            'verifiedMobile'       => $verifiedMobile
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

        return $this->render('TopxiaWebBundle:Settings:security-questions.html.twig', array(
            'hasSecurityQuestions' => $hasSecurityQuestions,
            'question1'            => $question1,
            'question2'            => $question2,
            'question3'            => $question3
        ));
    }

    public function securityQuestionsAction(Request $request)
    {
        $user                 = $this->getCurrentUser();
        $userSecureQuestions  = $this->getUserService()->getUserSecureQuestionsByUserId($user['id']);
        $hasSecurityQuestions = (isset($userSecureQuestions)) && (count($userSecureQuestions) > 0);

        if ($user->isLogin() && empty($user['password'])) {
            $request->getSession()->set('_target_path', $this->generateUrl('settings_security_questions'));
            return $this->redirect($this->generateUrl('settings_setup_password'));
        }

        if ($request->getMethod() == 'POST') {
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
                'securityQuestion1' => $request->request->get('question-1'),
                'securityAnswer1'   => $request->request->get('answer-1'),
                'securityQuestion2' => $request->request->get('question-2'),
                'securityAnswer2'   => $request->request->get('answer-2'),
                'securityQuestion3' => $request->request->get('question-3'),
                'securityAnswer3'   => $request->request->get('answer-3')
            );
            $this->getUserService()->addUserSecureQuestionsWithUnHashedAnswers($user['id'], $fields);
            $this->setFlashMessage('success', '安全问题设置成功。');
            $hasSecurityQuestions = true;
            $userSecureQuestions  = $this->getUserService()->getUserSecureQuestionsByUserId($user['id']);
        }

        return $this->securityQuestionsActionReturn($hasSecurityQuestions, $userSecureQuestions);
    }

    protected function bindMobileReturn($hasVerifiedMobile, $setMobileResult, $verifiedMobile)
    {
        return $this->render('TopxiaWebBundle:Settings:bind-mobile.html.twig', array(
            'hasVerifiedMobile' => $hasVerifiedMobile,
            'setMobileResult'   => $setMobileResult,
            'verifiedMobile'    => $verifiedMobile
        ));
    }

    public function bindMobileAction(Request $request)
    {
        $currentUser       = $this->getCurrentUser()->toArray();
        $verifiedMobile    = '';
        $hasVerifiedMobile = (isset($currentUser['verifiedMobile']) && (strlen($currentUser['verifiedMobile']) > 0));

        if ($hasVerifiedMobile) {
            $verifiedMobile = $currentUser['verifiedMobile'];
        }

        $setMobileResult = 'none';

        $scenario = "sms_bind";

        if ($this->setting('cloud_sms.sms_enabled') != '1' || $this->setting("cloud_sms.{$scenario}") != 'on') {
            return $this->render('TopxiaWebBundle:Settings:edu-cloud-error.html.twig', array());
        }

        $user = $this->getCurrentUser();

        if ($user->isLogin() && empty($user['password'])) {
            $request->getSession()->set('_target_path', $this->generateUrl('settings_bind_mobile'));
            return $this->redirect($this->generateUrl('settings_setup_password'));
        }

        if ($request->getMethod() == 'POST') {
            $password = $request->request->get('password');

            if (!$this->getAuthService()->checkPassword($currentUser['id'], $password)) {
                $this->setFlashMessage('danger', '您的登录密码错误');
                SmsToolkit::clearSmsSession($request, $scenario);
                return $this->bindMobileReturn($hasVerifiedMobile, $setMobileResult, $verifiedMobile);
            }

            list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, $scenario);

            if ($result) {
                $verifiedMobile = $sessionField['to'];
                $this->getUserService()->changeMobile($currentUser['id'], $verifiedMobile);

                $setMobileResult = 'success';
                $this->setFlashMessage('success', '绑定成功');
            } else {
                $setMobileResult = 'fail';
                $this->setFlashMessage('danger', '绑定失败，原短信失效');
            }
        }

        return $this->bindMobileReturn($hasVerifiedMobile, $setMobileResult, $verifiedMobile);
    }

    public function passwordCheckAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $password    = $request->request->get('value');

        if (strlen($password) > 0) {
            $passwordRight = $this->getUserService()->verifyPassword($currentUser['id'], $password);

            if ($passwordRight) {
                $response = array('success' => true, 'message' => '密码正确');
            } else {
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

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $passwords = $form->getData();

                if (!$this->getAuthService()->checkPassword($user['id'], $passwords['currentPassword'])) {
                    $this->setFlashMessage('danger', '当前密码不正确，请重试！');
                } else {
                    $this->getAuthService()->changePassword($user['id'], $passwords['currentPassword'], $passwords['newPassword']);
                    $this->setFlashMessage('success', '密码修改成功。');
                }

                return $this->redirect($this->generateUrl('settings_password'));
            }
        }

        return $this->render('TopxiaWebBundle:Settings:password.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function emailAction(Request $request)
    {
        $user       = $this->getCurrentUser();
        $mailer     = $this->getSettingService()->get('mailer', array());
        $cloudEmail = $this->getSettingService()->get('cloud_email', array());

        if (empty($user['setup'])) {
            return $this->redirect($this->generateUrl('settings_setup'));
        }

        $form = $this->createFormBuilder()
            ->add('password', 'password')
            ->add('email', 'text')
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $data         = $form->getData();
                $isPasswordOk = $this->getUserService()->verifyPassword($user['id'], $data['password']);

                if (!$isPasswordOk) {
                    $this->setFlashMessage('danger', '密码不正确，请重试。');
                    return $this->redirect($this->generateUrl('settings_email'));
                }

                $userOfNewEmail = $this->getUserService()->getUserByEmail($data['email']);

                if ($userOfNewEmail && $userOfNewEmail['id'] == $user['id']) {
                    $this->setFlashMessage('danger', '新邮箱，不能跟当前邮箱一样。');
                    return $this->redirect($this->generateUrl('settings_email'));
                }

                if ($userOfNewEmail && $userOfNewEmail['id'] != $user['id']) {
                    $this->setFlashMessage('danger', '新邮箱已经被注册，请换一个试试。');
                    return $this->redirect($this->generateUrl('settings_email'));
                }

                $token = $this->getUserService()->makeToken('email-verify', $user['id'], strtotime('+1 day'), $data['email']);

                try {
                    $site        = $this->setting('site', array());
                    $mailOptions = array(
                        'to'       => $data['email'],
                        'template' => 'email_reset_email',
                        'params'   => array(
                            'sitename'  => $site['name'],
                            'siteurl'   => $site['url'],
                            'verifyurl' => $this->generateUrl('auth_email_confirm', array('token' => $token), true),
                            'nickname'  => $user['nickname']
                        )
                    );
                    $mail = MailFactory::create($mailOptions);
                    $mail->send();
                    $this->setFlashMessage('success', "请到邮箱{$data['email']}中接收确认邮件，并点击确认邮件中的链接完成修改。");
                } catch (\Exception $e) {
                    $this->setFlashMessage('danger', "邮箱变更确认邮件发送失败，请联系管理员。");
                    $this->getLogService()->error('system', 'setting_email_change', '邮箱变更确认邮件发送失败:'.$e->getMessage());
                }
                return $this->redirect($this->generateUrl('settings_email'));
            }
        }

        return $this->render("TopxiaWebBundle:Settings:email.html.twig", array(
            'form'       => $form->createView(),
            'mailer'     => $mailer,
            'cloudEmail' => $cloudEmail
        ));
    }

    public function emailVerifyAction()
    {
        $user      = $this->getCurrentUser();
        $token     = $this->getUserService()->makeToken('email-verify', $user['id'], strtotime('+1 day'), $user['email']);
        $verifyurl = $this->generateUrl('register_email_verify', array('token' => $token), true);
        $site      = $this->setting('site', array());
        try {
            $mailOptions = array(
                'to'       => $user['email'],
                'template' => 'email_verify_email',
                'params'   => array(
                    'verifyurl' => $verifyurl,
                    'nickname'  => $user['nickname'],
                    'sitename'  => $site['name'],
                    'siteurl'   => $site['url']
                )
            );
            $mail = MailFactory::create($mailOptions);
            $mail->send();
            $this->setFlashMessage('success', "请到邮箱{$user['email']}中接收验证邮件，并点击邮件中的链接完成验证。");
        } catch (\Exception $e) {
            $this->getLogService()->error('system', 'setting_email-verify', '邮箱验证邮件发送失败:'.$e->getMessage());
            $this->setFlashMessage('danger', "邮箱验证邮件发送失败，请联系管理员。");
        }

        return $this->createJsonResponse(true);
    }

    public function bindsAction(Request $request)
    {
        $user      = $this->getCurrentUser();
        $clients   = OAuthClientFactory::clients();
        $userBinds = $this->getUserService()->findBindsByUserId($user->id) ?: array();

        foreach ($userBinds as $userBind) {
            if ($userBind['type'] == 'weixin') {
                $userBind['type'] = 'weixinweb';
            }

            $clients[$userBind['type']]['status'] = 'bind';
        }

        return $this->render('TopxiaWebBundle:Settings:binds.html.twig', array(
            'clients' => $clients
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
        $config   = array('key' => $settings[$type.'_key'], 'secret' => $settings[$type.'_secret']);
        $client   = OAuthClientFactory::create($type, $config);

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
            $this->setFlashMessage('danger', '您已经绑定了该第三方网站的帐号，不能重复绑定!');
            goto response;
        }

        $code = $request->query->get('code');

        if (empty($code)) {
            $this->setFlashMessage('danger', '您取消了授权/授权失败，请重试绑定!');
            goto response;
        }

        $callbackUrl = $this->generateUrl('settings_binds_bind_callback', array('type' => $type), true);
        try {
            $token = $this->createOAuthClient($type)->getAccessToken($code, $callbackUrl);
        } catch (\Exception $e) {
            $this->setFlashMessage('danger', '授权失败，请重试绑定!');
            goto response;
        }

        $bind = $this->getUserService()->getUserBindByTypeAndFromId($type, $token['userId']);

        if (!empty($bind)) {
            $this->setFlashMessage('danger', '该第三方帐号已经被其他帐号绑定，不能重复绑定!');
            goto response;
        }

        $this->getUserService()->bindUser($type, $token['userId'], $user['id'], $token);
        $this->setFlashMessage('success', '帐号绑定成功!');

        response:
        return $this->redirect($this->generateUrl('settings_binds'));
    }

    public function setupAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            $this->getAuthService()->changeEmail($user['id'], null, $data['email']);
            $this->getAuthService()->changeNickname($user['id'], $data['nickname']);
            $user = $this->getUserService()->setupAccount($user['id']);
            $this->authenticateUser($user);
            return $this->createJsonResponse(true);
        }

        return $this->render('TopxiaWebBundle:Settings:setup.html.twig');
    }

    public function setupPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $form = $this->createFormBuilder()
            ->add('newPassword', 'password')
            ->add('confirmPassword', 'password')
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $targetPath = $this->getTargetPath($request);
            $form->bind($request);

            if ($form->isValid()) {
                $passwords = $form->getData();
                $this->getUserService()->changePassword($user['id'], $passwords['newPassword']);
                return $this->redirect($targetPath);
            }
        }

        return $this->render('TopxiaWebBundle:Settings:setup-password.html.twig', array(
            'form' => $form->createView()
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

            if ($result == 'success') {
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
            throw new NotFoundHttpException();
        }
    }

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
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
            throw new \RuntimeException("第三方登录({$type})系统参数尚未配置，请先配置。");
        }

        if (!$settings[$type.'_enabled']) {
            throw new \RuntimeException("第三方登录({$type})未开启");
        }

        $config = array('key' => $settings[$type.'_key'], 'secret' => $settings[$type.'_secret']);
        $client = OAuthClientFactory::create($type, $config);

        return $client;
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getSensitiveService()
    {
        return $this->getServiceKernel()->createService('SensitiveWord:Sensitive.SensitiveService');
    }

    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

    protected function downloadImg($url)
    {
        $currentUser = $this->getCurrentUser();
        $filename    = md5($url).'_'.time();
        $filePath    = $this->container->getParameter('topxia.upload.public_directory').'/tmp/'.$currentUser['id'].'_'.time().'.jpg';

        $fp  = fopen($filePath, 'w');
        $img = fopen($url, 'r');
        stream_get_meta_data($img);

        while (!feof($img)) {
            $result .= fgets($img, 1024);
        }

        fclose($img);
        fwrite($fp, $result);
        fclose($fp);
        return $filePath;
    }
}
