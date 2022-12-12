<?php

namespace AppBundle\Controller;

use AppBundle\Common\CurlToolkit;
use AppBundle\Common\Exception\AccessDeniedException;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\SmsToolkit;
use AppBundle\Component\OAuthClient\OAuthClientFactory;
use Biz\Content\Service\FileService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\SCRM\Service\SCRMService;
use Biz\Sensitive\Service\SensitiveService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\System\SettingException;
use Biz\User\Service\AuthService;
use Biz\User\Service\UserFieldService;
use Biz\WeChat\Service\WeChatService;
use Codeages\Biz\Pay\Service\AccountService;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SettingsController extends BaseController
{
    public function profileAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $profile['title'] = $user['title'];
        if ('POST' === $request->getMethod()) {
            $profile = $request->request->get('profile');
            if (!((strlen($user['verifiedMobile']) > 0) && (isset($profile['mobile'])))) {
                $this->getUserService()->updateUserProfile($user['id'], $profile, false);
                $this->setFlashMessage('success', 'site.save.success');
            } else {
                $this->setFlashMessage('danger', 'user.settings.profile.unable_change_bind_mobile');
            }

            return $this->redirect($this->generateUrl('settings'));
        }
        $fields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
        if (array_key_exists('idcard', $profile) && '0' == $profile['idcard']) {
            $profile['idcard'] = '';
        }
        $fromCourse = $request->query->get('fromCourse');

        return $this->render('settings/profile.html.twig', [
            'profile' => $profile,
            'fields' => $fields,
            'fromCourse' => $fromCourse,
            'user' => $user,
        ]);
    }

    public function approvalSubmitAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $profile['idcard'] = substr_replace($profile['idcard'], '************', 4, 12);
        $approval = $this->getUserService()->getLastestApprovalByUserIdAndStatus($user['id'], $user['approvalStatus']);

        if ('POST' === $request->getMethod()) {
            $faceImg = $request->files->get('faceImg');
            $backImg = $request->files->get('backImg');

            if (abs(filesize($faceImg)) > 2 * 1024 * 1024 || abs(filesize($backImg)) > 2 * 1024 * 1024
                || !FileToolkit::isImageFile($backImg) || !FileToolkit::isImageFile($faceImg)
                || getimagesize($faceImg) == false || getimagesize($backImg) == false) {
                $this->setFlashMessage('danger', 'user.settings.verification.photo_require_tips');

                return $this->render('settings/approval.html.twig', [
                    'profile' => $profile,
                ]);
            }

            $directory = $this->container->getParameter('topxia.upload.private_directory').'/approval';
            $this->getUserService()->applyUserApproval(
                $user['id'],
                $request->request->all(),
                $faceImg,
                $backImg,
                $directory
            );

            return $this->redirect($this->generateUrl('setting_approval_submit'));
        }

        return $this->render('settings/approval.html.twig', [
            'profile' => $profile,
            'approval' => $approval,
        ]);
    }

    public function qualificationAction()
    {
        return $this->render('settings/qualification.html.twig', [
            'user_id' => $this->getCurrentUser()->getId(),
        ]);
    }

    public function nicknameAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $isNickname = $this->getSettingService()->get('user_partner');
        if (0 == $isNickname['nickname_enabled']) {
            return $this->redirect($this->generateUrl('settings'));
        }

        if ('POST' === $request->getMethod()) {
            $nickname = $request->request->get('nickname');

            if ($this->getSensitiveService()->scanText($nickname)) {
                return $this->createJsonResponse(['message' => 'user.settings.basic_info.illegal_nickname'], 403);
            }

            list($result, $message) = $this->getAuthService()->checkUsername($nickname);

            if ('success' !== $result && $user['nickname'] != $nickname) {
                return $this->createJsonResponse(['message' => $message], 403);
            }

            $this->getAuthService()->changeNickname($user['id'], $nickname);

            return $this->createJsonResponse(['message' => 'user.settings.basic_info.nickname_change_successfully']);
        }
    }

    public function nicknameCheckAction(Request $request)
    {
        $nickname = $request->query->get('value');
        $currentUser = $this->getUserService()->getCurrentUser();

        if ($currentUser['nickname'] == $nickname) {
            return $this->createJsonResponse(['success' => true, 'message' => '']);
        }

        list($result, $message) = $this->getAuthService()->checkUsername($nickname);

        if ('success' === $result) {
            $response = ['success' => true, 'message' => ''];
        } else {
            $response = ['success' => false, 'message' => $message];
        }

        return $this->createJsonResponse($response);
    }

    public function avatarCropModalAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if ('POST' === $request->getMethod()) {
            $options = $request->request->all();
            $this->getUserService()->changeAvatar($currentUser['id'], $options['images']);
            $user = $this->getUserService()->getUser($currentUser['id']);
            $avatar = $this->getWebExtension()->getFpath($user['largeAvatar']);

            return $this->createJsonResponse([
                'status' => 'success',
                'avatar' => $avatar,
            ]);
        }

        $fileId = $request->getSession()->get('fileId');
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 270, 270);

        return $this->render('settings/avatar-crop-modal.html.twig', [
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ]);
    }

    //传头像，新的交互
    public function profileAvatarCropModalAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if ('POST' === $request->getMethod()) {
            $options = $request->request->all();
            $result = $this->getUserService()->changeAvatar($currentUser['id'], $options['images']);
            $image = $this->getWebExtension()->getFpath($result['largeAvatar']);

            return $this->createJsonResponse([
                'image' => $image,
            ], 200);
        }

        return $this->render('settings/profile-avatar-crop-modal.html.twig');
    }

    public function assistantQrCodeCropModalAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if ('POST' === $request->getMethod()) {
            $options = $request->request->all();
            $result = $this->getUserService()->changeAssistantQrCode($currentUser['id'], $options['images']);
            $image = $this->getWebExtension()->getFpath($result['weChatQrCode']);

            return $this->createJsonResponse([
                'image' => $image,
            ], 200);
        }

        return $this->render('settings/assistant-qrcode-crop-modal.html.twig');
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
        $imgs = [
            'large' => ['200', '200'],
            'medium' => ['120', '120'],
            'small' => ['48', '48'],
        ];
        $options = [
            'x' => '0',
            'y' => '0',
            'x2' => '200',
            'y2' => '200',
            'w' => '200',
            'h' => '200',
            'width' => '200',
            'height' => '200',
            'imgs' => $imgs,
        ];

        if (empty($options['group'])) {
            $options['group'] = 'default';
        }

        $record = $this->getFileService()->uploadFile($groupCode, $file);
        $parsed = $this->getFileService()->parseFileUri($record['uri']);
        $filePaths = FileToolKit::cropImages($parsed['fullpath'], $options);

        $fields = [];

        foreach ($filePaths as $key => $value) {
            $file = $this->getFileService()->uploadFile($options['group'], new File($value));
            $fields[] = [
                'type' => $key,
                'id' => $file['id'],
            ];
        }

        if (isset($options['deleteOriginFile']) && 0 == $options['deleteOriginFile']) {
            $fields[] = [
                'type' => 'origin',
                'id' => $record['id'],
            ];
        } else {
            $this->getFileService()->deleteFileByUri($record['uri']);
        }

        $this->getUserService()->changeAvatar($currentUser['id'], $fields);

        return $this->createJsonResponse(true);
    }

    public function securityAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $hasLoginPassword = strlen($user['password']) > 0;
        $hasPayPassword = $this->getAccountService()->isPayPasswordSetted($user['id']);
        $hasFindPayPasswordQuestion = $this->getAccountService()->isSecurityAnswersSetted($user['id']);
        $hasVerifiedMobile = (isset($user['verifiedMobile']) && (strlen($user['verifiedMobile']) > 0));
        $verifiedMobile = $hasVerifiedMobile ? $user['verifiedMobile'] : '';
        $hasEmail = strlen($user['email']) > 0 && false === stripos($user['email'], '@edusoho.net');

        $email = $hasEmail ? $user['email'] : '';
        $hasVerifiedEmail = $user['emailVerified'];

        $cloudSmsSetting = $this->getSettingService()->get('cloud_sms');
        $showBindMobile = (isset($cloudSmsSetting['sms_enabled'])) && ('1' == $cloudSmsSetting['sms_enabled'])
            && (isset($cloudSmsSetting['sms_bind'])) && ('on' == $cloudSmsSetting['sms_bind']);

        $itemScore = floor(100.0 / (4.0 + ($showBindMobile ? 1.0 : 0)));
        $progressScore = 1 + ($hasLoginPassword ? $itemScore : 0) + ($hasPayPassword ? $itemScore : 0) + ($hasFindPayPasswordQuestion ? $itemScore : 0) + ($showBindMobile && $hasVerifiedMobile ? $itemScore : 0) + ($hasVerifiedEmail ? $itemScore : 0);

        if ($progressScore <= 1) {
            $progressScore = 0;
        }

        return $this->render('settings/security.html.twig', [
            'progressScore' => $progressScore,
            'hasLoginPassword' => $hasLoginPassword,
            'hasPayPassword' => $hasPayPassword,
            'hasFindPayPasswordQuestion' => $hasFindPayPasswordQuestion,
            'hasVerifiedMobile' => $hasVerifiedMobile,
            'verifiedMobile' => $verifiedMobile,
            'hasEmail' => $hasEmail,
            'email' => $email,
            'hasVerifiedEmail' => $hasVerifiedEmail,
        ]);
    }

    public function payPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ('POST' === $request->getMethod()) {
            $passwords = $request->request->all();

            $validatePassed = $this->getAuthService()->checkPassword(
                $user['id'],
                $passwords['currentUserLoginPassword']
            );

            if (!$validatePassed) {
                return $this->createJsonResponse(
                    ['message' => 'user.settings.security.pay_password_set.incorrect_login_password'],
                    403
                );
            } else {
                $this->getAccountService()->setPayPassword($user['id'], $passwords['newPayPassword']);

                return $this->createJsonResponse(['message' => 'user.settings.security.pay_password_set.success']);
            }
        }

        return $this->render('settings/pay-password.html.twig');
    }

    public function setPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $form = $this->createFormBuilder()
            ->add('newPassword', PasswordType::class)
            ->add('confirmPassword', PasswordType::class)
            ->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $passwords = $form->getData();
                $this->getUserService()->changePassword($user['id'], $passwords['newPassword']);
                $form = $this->createFormBuilder()
                    ->add('currentUserLoginPassword', PasswordType::class)
                    ->add('newPayPassword', PasswordType::class)
                    ->add('confirmPayPassword', PasswordType::class)
                    ->getForm();

                return $this->render('settings/pay-password-modal.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
        }

        return $this->render('settings/password-modal.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function resetPayPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ($user->isLogin() && empty($user['password'])) {
            return $this->redirect(
                $this->generateUrl('settings_setup_password', ['targetPath' => 'settings_reset_pay_password'])
            );
        }

        if ('POST' === $request->getMethod()) {
            $passwords = $request->request->all();

            $validatePassed = $this->getAccountService()->validatePayPassword(
                $user['id'],
                $passwords['oldPayPassword']
            );

            if (!$validatePassed) {
                return $this->createJsonResponse(
                    ['message' => 'user.settings.security.pay_password_set.incorrect_pay_password'],
                    403
                );
            } else {
                $this->getAccountService()->setPayPassword($user['id'], $passwords['newPayPassword']);

                return $this->createJsonResponse(['message' => 'user.settings.security.pay_password_set.reset_success']
                );
            }
        }

        return $this->render('settings/reset-pay-password.html.twig');
    }

    protected function setPayPasswordPage($request, $userId)
    {
        $token = $this->getUserService()->makeToken('pay-password-reset', $userId, strtotime('+1 day'));
        $request->request->set('token', $token);

        return $this->forward('AppBundle:Settings:updatePayPassword', [
            'request' => $request,
        ]);
    }

    protected function updatePayPasswordReturn($form, $token)
    {
        return $this->render('settings/update-pay-password-from-email-or-secure-questions.html.twig', [
            'form' => $form->createView(),
            'token' => $token ?: null,
        ]);
    }

    public function updatePayPasswordAction(Request $request)
    {
        $token = $this->getUserService()->getToken(
            'pay-password-reset',
            $request->query->get('token') ?: $request->request->get('token')
        );

        if (empty($token)) {
            throw new \RuntimeException('Bad Token!');
        }

        $form = $this->createFormBuilder()
            ->add('payPassword', PasswordType::class)
            ->add('confirmPayPassword', PasswordType::class)
            ->add('currentUserLoginPassword', PasswordType::class)
            ->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                if ($data['payPassword'] != $data['confirmPayPassword']) {
                    $this->setFlashMessage(
                        'danger',
                        'user.settings.security.pay_password_set.twice_pay_password_mismatch'
                    );

                    return $this->updatePayPasswordReturn($form, $token);
                }

                if ($this->getAuthService()->checkPassword($token['userId'], $data['currentUserLoginPassword'])) {
                    $this->getAccountService()->setPayPassword($token['userId'], $data['payPassword']);
                    $this->getUserService()->deleteToken('pay-password-reset', $token['token']);

                    return $this->render('settings/pay-password-success.html.twig', [
                        'goto' => $this->generateUrl('settings_security', [], UrlGeneratorInterface::ABSOLUTE_URL),
                        'duration' => 3,
                    ]);
                } else {
                    $this->setFlashMessage(
                        'danger',
                        'user.settings.security.pay_password_set.incorrect_login_password'
                    );
                }
            }
        }

        return $this->updatePayPasswordReturn($form, $token);
    }

    public function findPayPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $hasLoginPassword = strlen($user['password']) > 0;
        $hasPayPassword = $this->getAccountService()->isPayPasswordSetted($user['id']);
        $userSecureQuestions = $this->getAccountService()->findSecurityAnswersByUserId($user['id']);
        $hasFindPayPasswordQuestion = (isset($userSecureQuestions)) && (count($userSecureQuestions) > 0);
        $hasVerifiedMobile = (isset($user['verifiedMobile']) && (strlen($user['verifiedMobile']) > 0));
        $verifiedMobile = $hasVerifiedMobile ? $user['verifiedMobile'] : '';

        return $this->render('settings/find-pay-password.html.twig', [
            'hasLoginPassword' => $hasLoginPassword,
            'hasPayPassword' => $hasPayPassword,
            'hasFindPayPasswordQuestion' => $hasFindPayPasswordQuestion,
            'hasVerifiedMobile' => $hasVerifiedMobile,
            'verifiedMobile' => $verifiedMobile,
        ]);
    }

    protected function findPayPasswordByQuestionActionReturn(
        $userSecureQuestions,
        $hasSecurityQuestions,
        $hasVerifiedMobile
    ) {
        $questionNum = mt_rand(0, 2);
        $questionKey = $userSecureQuestions[$questionNum]['question_key'];

        return $this->render('settings/find-pay-password-by-question.html.twig', [
            'questionKey' => $questionKey,
            'hasSecurityQuestions' => $hasSecurityQuestions,
            'hasVerifiedMobile' => $hasVerifiedMobile,
        ]);
    }

    public function findPayPasswordByQuestionAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $userSecureQuestions = $this->getAccountService()->findSecurityAnswersByUserId($user['id']);
        $hasSecurityQuestions = $this->getAccountService()->isSecurityAnswersSetted($user['id']);
        $verifiedMobile = $user['verifiedMobile'];
        $hasVerifiedMobile = null !== $verifiedMobile && strlen($verifiedMobile) > 0;
        $canSmsFind = ($hasVerifiedMobile) &&
            ('1' == $this->setting('cloud_sms.sms_enabled')) &&
            ('on' == $this->setting('cloud_sms.sms_forget_pay_password'));

        if ((!$hasSecurityQuestions) && ($canSmsFind)) {
            return $this->redirect($this->generateUrl('settings_find_pay_password_by_sms', []));
        }

        if (!$hasSecurityQuestions) {
            $this->setFlashMessage('danger', 'user.settings.security.pay_password_find.empty');

            return $this->forward('AppBundle:Settings:securityQuestions');
        }

        if ('POST' === $request->getMethod()) {
            $questionKey = $request->request->get('questionKey');
            $answer = $request->request->get('answer');

            $isAnswerRight = $this->getAccountService()->validateSecurityAnswer(
                $user['id'],
                $questionKey,
                $answer
            );

            if (!$isAnswerRight) {
                $this->setFlashMessage('danger', 'user.settings.security.pay_password_find.wrong_answer');

                return $this->findPayPasswordByQuestionActionReturn(
                    $userSecureQuestions,
                    $hasSecurityQuestions,
                    $hasVerifiedMobile
                );
            }

            $this->setFlashMessage('success', 'user.settings.security.pay_password_find.correct_answer');

            return $this->setPayPasswordPage($request, $user['id']);
        }

        return $this->findPayPasswordByQuestionActionReturn(
            $userSecureQuestions,
            $hasSecurityQuestions,
            $hasVerifiedMobile
        );
    }

    public function findPayPasswordBySmsAction(Request $request)
    {
        $scenario = 'sms_forget_pay_password';

        if ('1' != $this->setting('cloud_sms.sms_enabled') || 'on' !== $this->setting("cloud_sms.{$scenario}")) {
            return $this->render('settings/edu-cloud-error.html.twig', []);
        }

        $currentUser = $this->getCurrentUser();

        $hasSecurityQuestions = $this->getAccountService()->isSecurityAnswersSetted($currentUser['id']);
        $verifiedMobile = $currentUser['verifiedMobile'];
        $hasVerifiedMobile = null !== $verifiedMobile && strlen($verifiedMobile) > 0;

        if (!$hasVerifiedMobile) {
            $this->setFlashMessage('danger', 'user.settings.security.pay_password_find.unbind_mobile');

            return $this->redirect(
                $this->generateUrl('settings_bind_mobile', [
                ])
            );
        }

        if ('POST' === $request->getMethod()) {
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

        return $this->render('settings/find-pay-password-by-sms.html.twig', [
            'hasSecurityQuestions' => $hasSecurityQuestions,
            'hasVerifiedMobile' => $hasVerifiedMobile,
            'verifiedMobile' => $verifiedMobile,
        ]);
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

        return $this->render('settings/security-questions.html.twig', [
            'hasSecurityQuestions' => $hasSecurityQuestions,
            'question1' => $question1,
            'question2' => $question2,
            'question3' => $question3,
        ]);
    }

    public function securityQuestionsAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $userSecureQuestions = $this->getAccountService()->findSecurityAnswersByUserId($user['id']);
        $hasSecurityQuestions = (isset($userSecureQuestions)) && (count($userSecureQuestions) > 0);

        if ($user->isLogin() && empty($user['password'])) {
            return $this->redirect(
                $this->generateUrl('settings_setup_password', ['targetPath' => 'settings_security_questions'])
            );
        }

        if ('POST' === $request->getMethod()) {
            if (!$this->getAuthService()->checkPassword($user['id'], $request->request->get('userLoginPassword'))) {
                return $this->createJsonResponse(
                    ['message' => 'user.settings.security.questions.set.incorrect_password'],
                    403
                );
            }

            if ($hasSecurityQuestions) {
                return $this->createJsonResponse(
                    ['message' => 'user.settings.security.questions.set.not_modify_aligin_hint'],
                    403
                );
            }

            if ($request->request->get('question-1') == $request->request->get('question-2')
                || $request->request->get('question-1') == $request->request->get('question-3')
                || $request->request->get('question-2') == $request->request->get('question-3')) {
                return $this->createJsonResponse(
                    ['message' => 'user.settings.security.security_questions.type_duplicate_hint'],
                    403
                );
            }

            $fields[$request->request->get('question-1')] = $request->request->get('answer-1');
            $fields[$request->request->get('question-2')] = $request->request->get('answer-2');
            $fields[$request->request->get('question-3')] = $request->request->get('answer-3');

            $this->getAccountService()->setSecurityAnswers($user['id'], $fields);

            return $this->createJsonResponse(['message' => 'user.settings.security.questions.set.success']);
        }

        return $this->securityQuestionsActionReturn($hasSecurityQuestions, $userSecureQuestions);
    }

    public function bindMobileAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $verifiedMobile = '';
        $hasVerifiedMobile = (isset($user['verifiedMobile']) && (strlen($user['verifiedMobile']) > 0));

        if ($hasVerifiedMobile) {
            $verifiedMobile = $user['verifiedMobile'];
        }

        $setMobileResult = 'none';

        $scenario = 'sms_bind';

        if ('1' != $this->setting('cloud_sms.sms_enabled') || 'on' != $this->setting("cloud_sms.{$scenario}")) {
            return $this->render('settings/edu-cloud-error.html.twig', []);
        }

        if ($this->isSocialLogin($user)) {
            return $this->redirect(
                $this->generateUrl('settings_setup_password', ['targetPath' => 'settings_bind_mobile'])
            );
        }

        if ('POST' === $request->getMethod()) {
            $password = $request->request->get('password');

            if (!$this->getAuthService()->checkPassword($user['id'], $password)) {
                SmsToolkit::clearSmsSession($request, $scenario);

                return $this->createJsonResponse(['message' => 'site.incorrect.password'], 403);
            }

            list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, $scenario);

            if ($result) {
                $verifiedMobile = $sessionField['to'];
                $this->getUserService()->changeMobile($user['id'], $verifiedMobile);

                return $this->createJsonResponse(['message' => 'user.settings.security.mobile_bind.success']);
            } else {
                return $this->createJsonResponse(['message' => 'user.settings.security.mobile_bind.fail'], 403);
            }
        }

        return $this->render('settings/bind-mobile.html.twig', [
            'hasVerifiedMobile' => $hasVerifiedMobile,
            'setMobileResult' => $setMobileResult,
            'verifiedMobile' => $verifiedMobile,
        ]);
    }

    public function mobileBindAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $scenario = 'sms_bind';

        if ('1' != $this->setting('cloud_sms.sms_enabled') || 'on' != $this->setting("cloud_sms.{$scenario}")) {
            return $this->render('settings/edu-cloud-error.html.twig', []);
        }

        if ($this->isSocialLogin($user)) {
            return $this->redirect(
                $this->generateUrl('settings_setup_password', ['targetPath' => 'settings_bind_mobile'])
            );
        }

        $targetUrl = $this->getTargetPath($request) ?: $this->generateUrl('homepage');
        $mobileBindMode = $this->getSettingService()->node('login_bind.mobile_bind_mode', 'constraint');
        if ('option' === $mobileBindMode && (isset($_COOKIE['is_skip_mobile_bind']) && 1 == $_COOKIE['is_skip_mobile_bind'])) {
            return $this->redirect($targetUrl);
        }

        if ('POST' === $request->getMethod()) {
            list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, $scenario);

            if ($result) {
                $verifiedMobile = $sessionField['to'];
                $this->getUserService()->changeMobile($user['id'], $verifiedMobile);

                return $this->createJsonResponse(['message' => 'user.settings.security.mobile_bind.success']);
            } else {
                return $this->createJsonResponse(['message' => 'user.settings.security.mobile_bind.fail'], 403);
            }
        }

        return $this->render('settings/mobile-bind.html.twig', ['targetUrl' => $targetUrl]);
    }

    /**
     * if user login in  socail way such as QQ, user has no pasword
     *
     * @param  $user
     *
     * @return bool
     */
    private function isSocialLogin($user)
    {
        return $user->isLogin() && empty($user['password']);
    }

    public function passwordCheckAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $password = $request->request->get('value');
        $response = ['success' => true];
        if (strlen($password) > 0) {
            $passwordRight = $this->getUserService()->verifyPassword($currentUser['id'], $password);
            if (!$passwordRight) {
                $response = ['success' => false, 'message' => '密码错误'];
            }
        } else {
            $response = ['success' => false, 'message' => '密码不能为空'];
        }

        return $this->createJsonResponse($response);
    }

    public function passwordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ($user->isLogin() && empty($user['password'])) {
            return $this->redirect(
                $this->generateUrl('settings_setup_password', ['targetPath' => 'settings_password'])
            );
        }

        if ('POST' === $request->getMethod()) {
            $passwords = $request->request->all();
            $validatePassed = $this->getAuthService()->checkPassword($user['id'], $passwords['currentPassword']);

            if (!$validatePassed) {
                return $this->createJsonResponse(
                    ['message' => 'user.settings.security.password_modify.incorrect_password'],
                    403
                );
            } else {
                $this->getUserService()->initPassword($user['id'], $passwords['newPassword']);

                return $this->createJsonResponse(['message' => 'site.modify.success']);
            }
        }

        return $this->render('settings/password.html.twig');
    }

    public function emailAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $mailer = $this->getSettingService()->get('mailer', []);
        $cloudEmail = $this->getSettingService()->get('cloud_email_crm', []);

        if ($user->isLogin() && empty($user['password'])) {
            return $this->redirect($this->generateUrl('settings_setup_password', ['targetPath' => 'settings_email']));
        }

        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();

            //同一IP限制
            $biz = $this->getBiz();
            $rateLimiter = $biz['email_rate_limiter'];
            $rateLimiter->handle($request);

            //拖动校验
            $authSettings = $this->getSettingService()->get('auth', []);
            $this->dragCaptchaValidator($data, $authSettings);

            $isPasswordOk = $this->getUserService()->verifyPassword($user['id'], $data['password']);

            if (!$isPasswordOk) {
                return $this->createJsonResponse(['message' => 'site.incorrect.password'], 403);
            }

            $userOfNewEmail = $this->getUserService()->getUserByEmail($data['email']);

            if ($userOfNewEmail && $userOfNewEmail['id'] == $user['id']) {
                return $this->createJsonResponse(['message' => 'user.settings.email.new_email_same_old'], 403);
            }

            if ($userOfNewEmail && $userOfNewEmail['id'] != $user['id']) {
                return $this->createJsonResponse(['message' => 'user.settings.email.new_email_not_unique'], 403);
            }

            $tokenArgs = [
                'userId' => $user['id'],
                'duration' => 60 * 60 * 24,
                'data' => $data['email'],
            ];

            $token = $this->getTokenService()->makeToken('email-verify', $tokenArgs);
            $token = $token['token'];
            try {
                $site = $this->setting('site', []);
                $mailOptions = [
                    'to' => $data['email'],
                    'template' => 'email_reset_email',
                    'params' => [
                        'sitename' => $site['name'],
                        'siteurl' => $site['url'],
                        'verifyurl' => $this->generateUrl(
                            'auth_email_confirm',
                            ['token' => $token],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        ),
                        'nickname' => $user['nickname'],
                    ],
                ];
                $mailFactory = $this->getBiz()->offsetGet('mail_factory');
                $mail = $mailFactory($mailOptions);
                $mail->send();

                return $this->render(
                    'settings/email-verfiy.html.twig',
                    [
                        'message' => $this->get('translator')->trans(
                            'user.settings.email.send_success',
                            ['%email%' => $data['email']]
                        ),
                        'data' => [
                            'email' => $data['email'],
                        ],
                    ]
                );
            } catch (\Exception $e) {
                $this->getLogService()->error('system', 'setting_email_change', '邮箱变更确认邮件发送失败:'.$e->getMessage());

                return $this->createJsonResponse(['message' => 'user.settings.email.send_error'], 403);
            }
        }

        return $this->render('settings/email.html.twig', [
            'mailer' => $mailer,
            'cloudEmail' => $cloudEmail,
        ]);
    }

    public function emailVerifyAction()
    {
        $user = $this->getCurrentUser();
        $token = $this->getUserService()->makeToken('email-verify', $user['id'], strtotime('+1 day'), $user['email']);
        $verifyurl = $this->generateUrl(
            'register_email_verify',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $site = $this->setting('site', []);
        try {
            $mailOptions = [
                'to' => $user['email'],
                'template' => 'email_verify_email',
                'params' => [
                    'verifyurl' => $verifyurl,
                    'nickname' => $user['nickname'],
                    'sitename' => $site['name'],
                    'siteurl' => $site['url'],
                ],
            ];
            $mailFactory = $this->getBiz()->offsetGet('mail_factory');
            $mail = $mailFactory($mailOptions);
            $mail->send();

            return $this->render(
                'settings/email-verfiy.html.twig',
                [
                    'message' => $this->get('translator')->trans(
                        'user.settings.email.send_success',
                        ['%email%' => $user['email']]
                    ),
                    'data' => [
                        'email' => $user['email'],
                    ],
                ]
            );
        } catch (\Exception $e) {
            $this->getLogService()->error('system', 'setting_email-verify', '邮箱验证邮件发送失败:'.$e->getMessage());

            return $this->createJsonResponse(['message' => 'user.settings.email.send_error'], 403);
        }
    }

    public function bindsAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $clients = OAuthClientFactory::clients();
        $userBinds = $this->getUserService()->findBindsByUserId($user->id) ?: [];

        foreach ($userBinds as $userBind) {
            if ('weixin' === $userBind['type']) {
                $userBind['type'] = 'weixinweb';
            }

            $clients[$userBind['type']]['status'] = 'bind';
        }
        $wechatSetting = $this->getSettingService()->get('wechat', []);
        $loginQrcode = '';
        if (!empty($wechatSetting['wechat_notification_enabled'])) {
            $loginUrl = $this->generateUrl(
                'login',
                ['goto' => $wechatSetting['account_code']],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $loginQrcode = $this->generateUrl(
                'common_qrcode',
                ['text' => $loginUrl],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $this->render('settings/binds.html.twig', [
            'clients' => $clients,
            'loginQrcode' => $loginQrcode,
            'user' => $user,
        ]);
    }

    public function unBindAction(Request $request, $type)
    {
        $user = $this->getCurrentUser();
        $this->checkBindsName($type);
        $userBinds = $this->getUserService()->unBindUserByTypeAndToId($type, $user->id);

        return $this->createJsonResponse(['message' => 'user.settings.unbind_success']);
    }

    public function bindAction(Request $request, $type)
    {
        $this->checkBindsName($type);
        $callback = $this->generateUrl(
            'settings_binds_bind_callback',
            ['type' => $type],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $settings = $this->setting('login_bind');
        $config = ['key' => $settings[$type.'_key'], 'secret' => $settings[$type.'_secret']];
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

        $callbackUrl = $this->generateUrl(
            'settings_binds_bind_callback',
            ['type' => $type],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
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

    public function setupPasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $targetPath = $request->query->get('targetPath');
        $showType = $request->query->get('showType', 'modal');
        $form = $this->createFormBuilder()
            ->add('newPassword', PasswordType::class)
            ->add('confirmPassword', PasswordType::class)
            ->getForm();

        if ('POST' === $request->getMethod()) {
            if (!empty($user['password'])) {
                return $this->createJsonResponse([
                    'message' => 'user.settings.login_password_fail',
                ], 500);
            }
            $form->handleRequest($request);
            if ($form->isValid()) {
                $passwords = $form->getData();
                $this->getUserService()->changePassword($user['id'], $passwords['newPassword']);

                return $this->createJsonResponse([
                    'message' => 'user.settings.login_password_success',
                ]);
            } else {
                return $this->createJsonResponse([
                    'message' => 'user.settings.login_password_fail',
                ], 500);
            }
        }

        return $this->render('settings/setup-password.html.twig', [
            'targetPath' => $targetPath,
            'showType' => $showType,
            'form' => $form->createView(),
        ]);
    }

    public function setupCheckNicknameAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $nickname = $request->query->get('value');

        if ($nickname == $user['nickname']) {
            $response = ['success' => true];
        } else {
            list($result, $message) = $this->getAuthService()->checkUsername($nickname);

            if ('success' === $result) {
                $response = ['success' => true];
            } else {
                $response = ['success' => false, 'message' => $message];
            }
        }

        return $this->createJsonResponse($response);
    }

    public function scrmAction(Request $request)
    {
        if (!$this->getSCRMService()->isSCRMBind()) {
            throw new AccessDeniedException();
        }

        $currentUser = $this->getCurrentUser();
        $user = $this->getUserService()->getUser($currentUser->getId());
        if (1 == count($currentUser->getRoles())) {
            throw new AccessDeniedException();
        }

        $user = $this->getSCRMService()->setStaffSCRMData($user);
        $assistantQrCodeUrl = $this->generateAssistantQrCode($user);

        return $this->render('settings/scrm.html.twig', [
            'user' => $user,
            'assistantQrCodeUrl' => $assistantQrCodeUrl,
        ]);
    }

    protected function generateAssistantQrCode($user)
    {
        $url = $this->getSCRMService()->getStaffBindUrl($user);
        if (empty($url)) {
            return '';
        }

        $token = $this->getTokenService()->makeToken(
            'qrcode',
            [
                'userId' => $user['id'],
                'data' => [
                    'url' => $url,
                ],
                'times' => 1,
                'duration' => 3600,
            ]
        );
        $url = $this->generateUrl(
            'common_parse_qrcode',
            ['token' => $token['token']],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->generateUrl('common_qrcode', ['text' => $url], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function scrmBindAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $user = $this->getUserService()->getUser($user['id']);

        $user = $this->getSCRMService()->setStaffSCRMData($user);

        if (empty($user['scrmStaffId'])) {
            return $this->createJsonResponse(false);
        }

        return $this->createJsonResponse(true);
    }

    protected function checkBindsName($type)
    {
        $types = array_keys(OAuthClientFactory::clients());

        if (!in_array($type, $types)) {
            $this->createNewException(SettingException::OAUTH_CLIENT_TYPE_INVALID());
        }
    }

    public function fetchAvatar($url)
    {
        return CurlToolkit::request('GET', $url, [], ['contentType' => 'plain']);
    }

    protected function createOAuthClient($type)
    {
        $settings = $this->setting('login_bind');

        if (empty($settings)) {
            $this->createNewException(SettingException::NOTFOUND_THIRD_PARTY_AUTH_CONFIG());
        }

        if (empty($settings) || !isset($settings[$type.'_enabled']) || empty($settings[$type.'_key']) || empty($settings[$type.'_secret'])) {
            throw new \RuntimeException('第三方登录('.$type.')系统参数尚未配置，请先配置。');
        }

        if (!$settings[$type.'_enabled']) {
            throw new \RuntimeException('第三方登录('.$type.')未开启');
        }

        $config = ['key' => $settings[$type.'_key'], 'secret' => $settings[$type.'_secret']];
        $client = OAuthClientFactory::create($type, $config);

        return $client;
    }

    protected function dragCaptchaValidator($registration, $authSettings)
    {
        if (array_key_exists(
                'captcha_enabled',
                $authSettings
            ) && (1 == $authSettings['captcha_enabled']) && empty($registration['mobile'])) {
            $biz = $this->getBiz();
            $bizDragCaptcha = $biz['biz_drag_captcha'];

            $dragcaptchaToken = empty($registration['dragCaptchaToken']) ? '' : $registration['dragCaptchaToken'];
            $bizDragCaptcha->check($dragcaptchaToken);
        }
    }

    /**
     * @return SCRMService
     */
    protected function getSCRMService()
    {
        return $this->getBiz()->service('SCRM:SCRMService');
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

    /**
     * @return AccountService
     */
    protected function getAccountService()
    {
        return $this->getBiz()->service('Pay:AccountService');
    }

    /**
     * @return WeChatService
     */
    protected function getWeChatService()
    {
        return $this->getBiz()->service('WeChat:WeChatService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->getBiz()->service('MultiClass:MultiClassService');
    }

    protected function downloadImg($url)
    {
        $currentUser = $this->getCurrentUser();
        //        $filename    = md5($url).'_'.time();
        $filePath = $this->container->getParameter(
                'topxia.upload.public_directory'
            ).'/tmp/'.$currentUser['id'].'_'.time().'.jpg';

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
