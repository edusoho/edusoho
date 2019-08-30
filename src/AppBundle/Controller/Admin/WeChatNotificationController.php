<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Notification\Service\NotificationService;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Biz\WeChat\Service\WeChatService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Component\Notification\WeChatTemplateMessage\TemplateUtil;
use Biz\CloudPlatform\CloudAPIFactory;

class WeChatNotificationController extends BaseController
{
    public function recordAction(Request $request)
    {
        $paginator = new Paginator(
            $request,
            $this->getNotificationService()->countBatches(array()),
            20
        );
        $notifications = $this->getNotificationService()->searchBatches(
            array(),
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $notifications = ArrayToolkit::index($notifications, 'id');
        $notificationIds = ArrayToolkit::column($notifications, 'eventId');
        $this->getNotificationService()->batchHandleNotificationResults($notifications);
        $notificationEvents = $this->getNotificationService()->findEventsByIds($notificationIds);
        $notificationEvents = ArrayToolkit::index($notificationEvents, 'id');

        return $this->render('admin/wechat-notification/index.html.twig', array(
            'notifications' => $notifications,
            'notificationEvents' => $notificationEvents,
            'paginator' => $paginator,
        ));
    }

    public function recordDetailAction(Request $request, $id)
    {
        $notification = $this->getNotificationService()->getEvent($id);

        return $this->render('admin/wechat-notification/notification-modal.html.twig', array(
            'notification' => $notification,
        ));
    }

    public function fansListAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->filterConditions($conditions);
        $conditions['subscribeTimeNotEqual'] = 0;

        $currentNum = $this->getWeChatService()->countWeChatUserJoinUser($conditions);
        $paginator = new Paginator(
            $request,
            $currentNum,
            20
        );

        $fans = $this->getWeChatService()->searchWeChatUsersJoinUser(
            $conditions,
            array('subscribeTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/wechat-notification/fans-list.html.twig', array(
            'fans' => $fans,
            'paginator' => $paginator,
            'currentNum' => $currentNum,
        ));
    }

    public function manageAction(Request $request)
    {
        $wechatDefault = $this->getDefaultWechatSetting();
        $wechatSetting = $this->getSettingService()->get('wechat', array());
        $wechatSetting = array_merge($wechatDefault, $wechatSetting);
        $templates = $this->getTemplateSetting(TemplateUtil::templates(), $wechatSetting);

        return $this->render('admin/wechat-notification/manage.html.twig', array(
            'wechatSetting' => $wechatSetting,
            'templates' => $templates,
            'isCloudOpen' => $this->isCloudOpen(),
        ));
    }

    public function showAction(Request $request)
    {
        $key = $request->query->get('key');
        $templates = TemplateUtil::templates();

        return $this->render('admin/wechat-notification/template-modal.html.twig', array(
            'template' => $templates[$key],
        ));
    }

    public function showRuleAction(Request $request)
    {
        $key = $request->query->get('key');
        $templates = TemplateUtil::templates();

        return $this->render('admin/wechat-notification/rule-modal.html.twig', array(
            'template' => $templates[$key],
        ));
    }

    public function statusAction(Request $request)
    {
        $key = $request->query->get('key');
        $fields = $request->request->all();
        $templates = TemplateUtil::templates();
        $template = $templates[$key];
        $wechatSetting = $this->getSettingService()->get('wechat', array());
        if (empty($wechatSetting['wechat_notification_enabled'])) {
            throw new \RuntimeException($this->trans('wechat.notification.service_not_open'));
        }

        if ($fields['isEnable']) {
            $this->addTemplate($template, $key);
            $this->setTemplateDetail($key, $fields);
        } else {
            $this->deleteTemplate($template, $key);
        }

        return $this->createJsonResponse(true);
    }

    public function homeworkReviewTemplateStatusAction(Request $request)
    {
        $wechatSetting = $this->getSettingService()->get('wechat', array());

        return $this->render('admin/wechat-notification/homework-review-notification-setting-modal.html.twig', array(
            'sendTime' => isset($wechatSetting['homeworkOrTestPaperReview']['sendTime']) ? $wechatSetting['homeworkOrTestPaperReview']['sendTime'] : '',
            'key' => 'homeworkOrTestPaperReview',
        ));
    }

    public function liveTemplateStatusAction(Request $request)
    {

    }

    protected function filterConditions($conditions)
    {
        if (isset($conditions['weChatFansType'])) {
            if ('user' == $conditions['weChatFansType']) {
                $conditions['userIdNotEqual'] = 0;
            }

            if ('notUser' == $conditions['weChatFansType']) {
                $conditions['userId'] = 0;
            }

            unset($conditions['weChatFansType']);
        }

        if (isset($conditions['weChatFansKeywordType'])) {
            if ('wechatNickname' == $conditions['weChatFansKeywordType']) {
                $conditions['wechatname'] = $conditions['keyword'];
            }

            if ('nickname' == $conditions['weChatFansKeywordType']) {
                $conditions['nickname'] = $conditions['keyword'];
            }

            if (!empty($conditions['keyword'])) {
                unset($conditions['keyword']);
            }
        }
        unset($conditions['weChatFansKeywordType']);

        return $conditions;
    }

    protected function addTemplate($template, $key)
    {
        $client = $this->getTemplateClient();
        if (empty($client)) {
            throw new \RuntimeException($this->trans('wechat.notification.empty_token'));
        }

        $wechatSetting = $this->getSettingService()->get('wechat');
        if (empty($wechatSetting[$key]['templateId'])) {
            $data = $client->addTemplate($template['id']);

            if (empty($data)) {
                throw new \RuntimeException($this->trans('wechat.notification.template_open_error'));
            }

            $wechatSetting[$key]['templateId'] = $data['template_id'];
        }

        $wechatSetting[$key]['status'] = 1;
        $this->getSettingService()->set('wechat', $wechatSetting);

        return $this->getSettingService()->get('wechat', $wechatSetting);
    }

    protected function setTemplateDetail($key, $fields)
    {
        $wechatSetting = $this->getSettingService()->get('wechat', array());

        if ('homeworkOrTestPaperReview' == $key) {
            $wechatSetting['homeworkOrTestPaperReview']['sendTime'] = $fields['sendTime'];
            $this->getSettingService()->set('wechat', array());
            if (!empty($wechatSetting['homeworkOrTestPaperReview']['templateId']) && !empty($wechatSetting['homeworkOrTestPaperReview']['sendTime'])) {
                $expression = $this->getSendTimeExpression($fields['sendTime']);
                $notificationJob = $this->getSchedulerService()->getJobByName('WeChatNotificationJob_HomeWorkOrTestPaperReview');
                if ($notificationJob) {
                    $this->getSchedulerService()->deleteJob($notificationJob['id']);
                }
                $job = array(
                    'name' => 'WeChatNotificationJob_HomeWorkOrTestPaperReview',
                    'expression' => $expression,
                    'class' => 'Biz\WeChatNotification\Job\HomeWorkOrTestPaperReviewNotificationJob',
                    'misfire_policy' => 'executing',
                    'args' => array(
                        'key' => $key,
                        'sendTime' => $wechatSetting['homeworkOrTestPaperReview']['sendTime'],
                    ),
                );
                $this->getSchedulerService()->register($job);
            }
        }

        return $this->getSettingService()->set('wechat', $wechatSetting);
    }

    protected function getSendTimeExpression($sendTime)
    {
        $expression = '';
        if (!is_array($sendTime)) {
            $hourAndMinute = explode(':', $sendTime);
            $minute = ($hourAndMinute[1] < 10) ? $hourAndMinute[1] % 10 : $hourAndMinute[1];
            $hour = ($hourAndMinute[0] < 10) ? $hourAndMinute[0] % 10 : $hourAndMinute[0];

            $expression = $minute.' '.$hour.' * * *';
        }

        return $expression;
    }

    protected function deleteTemplate($template, $key)
    {
        $client = $this->getTemplateClient();
        if (empty($client)) {
            throw new \RuntimeException($this->trans('wechat.notification.empty_token'));
        }

        $wechatSetting = $this->getSettingService()->get('wechat');

        if (empty($wechatSetting[$key]['templateId'])) {
            throw new \RuntimeException($this->trans('wechat.notification.template_not_exist'));
        }

        $data = $client->deleteTemplate($wechatSetting[$key]['templateId']);

        if (empty($data)) {
            throw new \RuntimeException($this->trans('wechat.notification.template_open_error'));
        }

        $wechatSetting[$key]['templateId'] = '';
        $wechatSetting[$key]['status'] = 0;

        return $this->getSettingService()->set('wechat', $wechatSetting);
    }

    protected function isCloudOpen()
    {
        try {
            $api = CloudAPIFactory::create('root');
            $info = $api->get('/me');
        } catch (\RuntimeException $e) {
            return false;
        }

        if (empty($info['accessCloud'])) {
            return false;
        }

        return true;
    }

    private function getTemplateSetting($templates, $wechatSetting)
    {
        foreach ($templates as $key => &$template) {
            $template['status'] = empty($wechatSetting[$key]['status']) ? 0 : $wechatSetting[$key]['status'];
        }

        return $templates;
    }

    private function getTemplateClient()
    {
        $biz = $this->getBiz();

        return $biz['wechat.template_message_client'];
    }

    private function getDefaultWechatSetting()
    {
        return array(
            'wechat_notification_enabled' => 0,
            'account_code' => '',
        );
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->createService('Notification:NotificationService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    /**
     * @return WeChatService
     */
    protected function getWeChatService()
    {
        return $this->createService('WeChat:WeChatService');
    }
}
