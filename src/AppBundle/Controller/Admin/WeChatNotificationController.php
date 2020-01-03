<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Notification\Service\NotificationService;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Biz\WeChat\Service\WeChatService;
use Symfony\Component\HttpFoundation\Request;
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
        $wechatSetting = $this->getSettingService()->get('wechat', array());

        if (isset($wechatSetting['wechat_notification_enabled']) && 1 == $wechatSetting['wechat_notification_enabled']) {
            $currentNum = $this->getWeChatService()->countWeChatUserJoinUser($conditions);
            $paginator = new Paginator(
                $request,
                $currentNum,
                10
            );

            $fans = $this->getWeChatService()->searchWeChatUsersJoinUser(
                $conditions,
                array('subscribeTime' => 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }

        return $this->render('admin/wechat-notification/fans-list.html.twig', array(
            'fans' => isset($fans) ? $fans : array(),
            'paginator' => isset($paginator) ? $paginator : array(),
            'currentNum' => isset($currentNum) ? $currentNum : 0,
            'wechatSetting' => $wechatSetting,
        ));
    }

    public function manageAction(Request $request)
    {
        $wechatDefault = $this->getDefaultWechatSetting();
        $wechatSetting = $this->getSettingService()->get('wechat', array());
        $wechatSetting = array_merge($wechatDefault, $wechatSetting);
        $templates = $this->get('extension.manager')->getWeChatTemplates();
        $templates = $this->getTemplateSetting($templates, $wechatSetting);

        return $this->render('admin/wechat-notification/manage.html.twig', array(
            'wechatSetting' => $wechatSetting,
            'templates' => $templates,
            'isCloudOpen' => $this->isCloudOpen(),
        ));
    }

    public function showAction(Request $request)
    {
        $key = $request->query->get('key');
        $templates = $this->get('extension.manager')->getWeChatTemplates();

        return $this->render('admin/wechat-notification/template-modal.html.twig', array(
            'template' => $templates[$key],
        ));
    }

    public function settingModalAction(Request $request)
    {
        $key = $request->query->get('key');
        $wechatSetting = $this->getSettingService()->get('wechat', array());
        $templates = $this->get('extension.manager')->getWeChatTemplates();
        $templates = $this->getTemplateSetting($templates, $wechatSetting);

        if ('POST' == $request->getMethod()) {
            if (empty($wechatSetting['wechat_notification_enabled'])) {
                throw new \RuntimeException($this->trans('wechat.notification.service_not_open'));
            }
            $fields = $request->request->all();
            if (1 == $fields['status']) {
                $this->getWeChatService()->addTemplate($templates[$key], $key);
            } else {
                $this->getWeChatService()->deleteTemplate($templates[$key], $key);
            }
            $this->getWeChatService()->saveWeChatTemplateSetting($key, $fields);

            return $this->createJsonResponse(true);
        }
        $modal = isset($templates[$key]['setting_modal']) ? $templates[$key]['setting_modal'] : 'admin/wechat-notification/setting-modal/default-modal.html.twig';

        return $this->render($modal, array(
            'template' => $templates[$key],
            'wechatSetting' => $wechatSetting,
            'key' => $key,
        ));
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
                $conditions['wechatname'] = urlencode($conditions['keyword']);
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
            $template = empty($wechatSetting['templates'][$key]) ? $template : array_merge($template, $wechatSetting['templates'][$key]);
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

    /**
     * @return \QiQiuYun\SDK\Service\WeChatService
     */
    protected function getSDKWeChatService()
    {
        $biz = $this->getBiz();

        return $biz['qiQiuYunSdk.wechat'];
    }
}
