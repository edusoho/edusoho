<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Coupon\Service\CouponBatchService;
use Biz\Course\Service\CourseSetService;
use Biz\System\Service\SettingService;
use Biz\Theme\Service\ThemeService;
use Symfony\Component\HttpFoundation\Request;

class OperationSettingController extends BaseController
{
    public function articleSetAction(Request $request)
    {
        $articleSetting = $this->getSettingService()->get('article', []);
        $reviewEnabled = $this->getSettingService()->node('ugc_review.enable_review', '1');

        $default = [
            'name' => '资讯频道',
            'pageNums' => 20,
            'show_comment' => $reviewEnabled ?
                $this->getSettingService()->node('ugc_review.enable_article_review', '1') :
                0,
        ];

        $articleSetting = array_merge($default, $articleSetting);

        if ('POST' == $request->getMethod()) {
            $articleSetting = array_merge($default, $request->request->all());
            $this->getSettingService()->set('article', $articleSetting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/system/operation/article-set.html.twig', [
            'articleSetting' => $articleSetting,
        ]);
    }

    public function groupSetAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();

            $this->getSettingService()->set('group', $set);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/system/operation/group-set.html.twig', [
        ]);
    }

    public function teacherQualificationSetAction(Request $request)
    {
        $default = [
            'qualification_enabled' => 0,
        ];

        if ('POST' == $request->getMethod()) {
            $qualificationSetting = $request->request->all();

            $currentTheme = $this->getSettingService()->get('theme');

            if ('0' === $qualificationSetting['qualification_enabled'] && '简墨' === $currentTheme['name']) {
                $this->getThemeService()->cancelConfirmConfigByName('简墨', 'TeacherQualification');
            }
            $this->getSettingService()->set('qualification', $qualificationSetting);

            return $this->createJsonResponse(true);
        }

        $qualificationSetting = $this->getSettingService()->get('qualification', []);
        $qualificationSetting = array_merge($default, $qualificationSetting);

        return $this->render('admin-v2/system/operation/qualification-set.html.twig', [
            'qualificationSetting' => $qualificationSetting,
        ]);
    }

    public function inviteSetAction(Request $request)
    {
        $default = [
            'invite_code_setting' => 0,
            'promoted_user_enable' => 0,
            'promoted_user_batchId' => '',
            'promote_user_enable' => 0,
            'promote_user_batchId' => '',
            'get_coupon_setting' => 1,
            'inviteInfomation_template' => '{{registerUrl}}',
            'remain_number' => '',
            'mobile' => '',
        ];

        if ('POST' == $request->getMethod()) {
            $inviteSetting = $request->request->all();
            if (!empty($inviteSetting['promoted_user_batchId']) || !empty($inviteSetting['promoted_user_enable'])) {
                $batch = $this->getCouponBatchService()->getBatch($inviteSetting['promoted_user_batchId']);
                if ($batch['unreceivedNum'] <= 1) {
                    return  $this->createJsonResponse(['status' => false, 'message' => $this->trans('admin.setting.invite.chooser_coupon.unreceived_num')]);
                }
            }
            if (!empty($inviteSetting['promote_user_batchId']) || !empty($inviteSetting['promote_user_enable'])) {
                $batch = $this->getCouponBatchService()->getBatch($inviteSetting['promote_user_batchId']);
                if ($batch['unreceivedNum'] <= 1) {
                    return  $this->createJsonResponse(['status' => false, 'message' => $this->trans('admin.setting.invite.chooser_coupon.unreceived_num')]);
                }
            }
            $inviteSetting = ArrayToolkit::parts($inviteSetting, [
                'invite_code_setting',
                'promoted_user_enable',
                'promoted_user_batchId',
                'promote_user_enable',
                'promote_user_batchId',
                'get_coupon_setting',
                'inviteInfomation_template',
                'remain_number',
                'mobile',
            ]);

            $inviteSetting = array_merge($default, $inviteSetting);
            $inviteSetting['promoted_sms_send'] = 1;
            $inviteSetting['promote_sms_send'] = 1;
            if (!empty($inviteSetting['remain_number']) && !empty($inviteSetting['mobile'])) {
                $inviteSetting = $this->updateInviteSmsSendSetting($inviteSetting);
            }

            $this->getSettingService()->set('invite', $inviteSetting);

            return $this->createJsonResponse(true);
        }

        $inviteSetting = $this->getSettingService()->get('invite', []);
        $inviteSetting = array_merge($default, $inviteSetting);

        return $this->render('admin-v2/system/operation/invite-set.html.twig', [
            'inviteSetting' => $inviteSetting,
            'inviteInfomation_template' => $inviteSetting['inviteInfomation_template'],
        ]);
    }

    public function messageOpenAction(Request $request)
    {
        $message = $this->getSettingService()->get('message', []);

        $default = [
            'showable' => '1',
        ];

        $message = array_merge($default, $message);

        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();

            $message = array_merge($message, $set);

            $this->getSettingService()->set('message', $set);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/system/operation/message-open.html.twig', [
            'messageSetting' => $message,
        ]);
    }

    public function messageSendAction(Request $request)
    {
        $messageSettingDefault = [
            'studentToStudent' => 1,
            'studentToTeacher' => 1,
            'teacherToStudent' => 1,
        ];
        $setting = $this->getSettingService()->get('message', []);
        $setting = array_merge($messageSettingDefault, $setting);
        $this->getSettingService()->set('message', $setting);

        if ('POST' == $request->getMethod()) {
            $formData = $request->request->all();
            $formData = ArrayToolkit::parts($formData, ['studentToStudent', 'studentToTeacher', 'teacherToStudent']);
            $formData = array_merge(['studentToStudent' => 0, 'studentToTeacher' => 0, 'teacherToStudent' => 0], $formData);

            $this->getSettingService()->set('message', $formData);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/system/operation/message-send.html.twig');
    }

    public function chooseCouponAction(Request $request, $type)
    {
        $conditions = $request->query->all();
        $conditions['deadlineMode'] = 'day';
        $conditions['unreceivedNumGt'] = 1;

        $paginator = new Paginator(
            $request,
            $this->getCouponBatchService()->searchBatchsCount($conditions),
            10
        );

        $batchs = $this->getCouponBatchService()->searchBatchs(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($batchs as $key => &$batch) {
            $batch['couponContent'] = $this->getCouponBatchService()->getCouponBatchContent($batch['id']);
        }

        return $this->render('admin-v2/system/operation/choose-coupon/chooser-coupon-modal.html.twig', [
            'batchs' => $batchs,
            'type' => $type,
            'paginator' => $paginator,
        ]);
    }

    public function chooseResourceListAction(Request $request, $batchId)
    {
        $batch = $this->getCouponBatchService()->getBatch($batchId);
        if (!in_array($batch['targetType'], ['course', 'classroom']) || $batch['targetId'] < 0) {
            $this->createNewException(CouponException::TARGET_TYPE_ERROR());
        }
        $resourceIds = empty($batch['targetIds']) ? [-1] : $batch['targetIds'];
        if ('course' == $batch['targetType']) {
            $resources = $this->getCourseSetService()->findCourseSetsByIds($resourceIds);
        } else {
            $resources = $this->getClassroomService()->findClassroomsByIds($resourceIds);
        }

        return $this->render('admin-v2/system/operation/choose-coupon/coupon-batch-resource-list-modal.html.twig', [
            'batch' => $batch,
            'resources' => $resources,
        ]);
    }

    protected function updateInviteSmsSendSetting($inviteSetting)
    {
        if ($inviteSetting['promoted_user_enable']) {
            $batch = $this->getCouponBatchService()->getBatch($inviteSetting['promoted_user_batchId']);
            if (!empty($batch) && $inviteSetting['remain_number'] <= $batch['unreceivedNum']) {
                $inviteSetting['promoted_sms_send'] = 0;
            }
        }

        if ($inviteSetting['promote_user_enable']) {
            $batch = $this->getCouponBatchService()->getBatch($inviteSetting['promote_user_batchId']);
            if (!empty($batch) && $inviteSetting['remain_number'] <= $batch['unreceivedNum']) {
                $inviteSetting['promote_sms_send'] = 0;
            }
        }

        return $inviteSetting;
    }

    public function mailerAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin-v2/system/operation/mailer-set.html.twig', []);
        }

        $mailer = $this->getSettingService()->get('mailer', []);
        $default = [
            'enabled' => 0,
            'host' => '',
            'port' => '',
            'username' => '',
            'password' => '',
            'from' => '',
            'name' => '',
        ];
        $mailer = array_merge($default, $mailer);
        if ($request->isMethod('POST')) {
            $mailer = $request->request->all();
            $this->getSettingService()->set('mailer', $mailer);
            $mailerWithoutPassword = $mailer;
            $mailerWithoutPassword['password'] = '******';
            $this->setFlashMessage('success', 'site.save.success');
        }

        $status = $this->checkMailerStatus();

        $cloudMailName = '';

        return $this->render('admin-v2/system/operation/mailer-set.html.twig', [
            'mailer' => $mailer,
            'status' => $status,
            'cloudMailName' => $cloudMailName,
        ]);
    }

    public function mailerTestAction(Request $request)
    {
        $user = $this->getUser();
        $mailOptions = [
            'to' => $user['email'],
            'template' => 'email_system_self_test',
        ];
        $mailFactory = $this->getBiz()->offsetGet('mail_factory');
        $mail = $mailFactory($mailOptions);

        try {
            $mail->send();

            return $this->createJsonResponse([
                'status' => true,
            ]);
        } catch (\Exception $e) {
            return $this->createJsonResponse([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /*
     * 当前云邮件字段为cloud_email_crm
     */
    protected function checkMailerStatus()
    {
        $cloudEmail = $this->getSettingService()->get('cloud_email_crm', []);
        $mailer = $this->getSettingService()->get('mailer', []);

        if (!empty($cloudEmail) && 'enable' === $cloudEmail['status']) {
            return 'cloud_email_crm';
        }

        if (!empty($mailer) && 1 == $mailer['enabled']) {
            return 'email';
        }

        return '';
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return ThemeService
     */
    protected function getThemeService()
    {
        return $this->createService('Theme:ThemeService');
    }

    /**
     * @return CouponBatchService
     */
    protected function getCouponBatchService()
    {
        return $this->createService('Coupon:CouponBatchService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
