<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Notification\Service\NotificationService;
use Biz\System\Service\SettingService;
use Biz\WeChat\Service\WeChatService;
use Symfony\Component\HttpFoundation\Request;

class WeChatNotificationController extends BaseController
{
    public function manageAction(Request $request)
    {
        $wechatDefault = $this->getDefaultWechatSetting();
        $wechatSetting = $this->getSettingService()->get('wechat', []);
        $wechatNotificationSetting = $this->getSettingService()->get('wechat_notification', $this->getDefaultWechatNotification());
        $wechatSetting = array_merge($wechatDefault, $wechatSetting, $wechatNotificationSetting);
        $templates = $this->get('extension.manager')->getWeChatTemplates();
        $templates = $this->getTemplateSetting($templates, $wechatSetting);

        return $this->render('admin-v2/operating/wechat-notification/manage.html.twig', [
            'wechatSetting' => $wechatSetting,
            'templates' => $templates,
            'isCloudOpen' => $this->isCloudOpen(),
        ]);
    }

    public function indexAction(Request $request)
    {
        $paginator = new Paginator(
            $request,
            $this->getNotificationService()->countBatches([]),
            20
        );
        $notifications = $this->getNotificationService()->searchBatches(
            [],
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $notifications = ArrayToolkit::index($notifications, 'id');
        $notificationIds = ArrayToolkit::column($notifications, 'eventId');
        $this->getNotificationService()->batchHandleNotificationResults($notifications);
        $notificationEvents = $this->getNotificationService()->findEventsByIds($notificationIds);
        $notificationEvents = ArrayToolkit::index($notificationEvents, 'id');

        return $this->render('admin-v2/operating/wechat-notification/index.html.twig', [
            'notifications' => $notifications,
            'notificationEvents' => $notificationEvents,
            'paginator' => $paginator,
        ]);
    }

    public function recordDetailAction(Request $request, $id)
    {
        $notification = $this->getNotificationService()->getEvent($id);

        return $this->render('admin-v2/operating/wechat-notification/notification-modal.html.twig', [
            'notification' => $notification,
        ]);
    }

    public function showAction(Request $request)
    {
        $key = $request->query->get('key');
        $templates = $this->get('extension.manager')->getWeChatTemplates();

        return $this->render('admin-v2/operating/wechat-notification/template-modal.html.twig', [
            'template' => $templates[$key],
        ]);
    }

    public function settingModalAction(Request $request)
    {
        $key = $request->query->get('key');
        $wechatSetting = $this->getSettingService()->get('wechat', []);
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
        $modal = isset($templates[$key]['setting_modal_v2']) ? $templates[$key]['setting_modal_v2'] : 'admin-v2/operating/wechat-notification/setting-modal/default-modal.html.twig';

        return $this->render($modal, [
            'template' => $templates[$key],
            'wechatSetting' => $wechatSetting,
            'key' => $key,
        ]);
    }

    public function settingTypeAction(Request $request)
    {
        $notification_type = $request->request->get('notification_type');
        $notification_sms = $request->request->get('notification_sms');
        $wechat_notification_config = $this->prepareWechatNotificationSetting($notification_type, $notification_sms);
        $this->getSettingService()->set('wechat_notification', $wechat_notification_config);

        return $this->createJsonResponse(true);
    }

    private function prepareWechatNotificationSetting($notification_type, $notification_sms)
    {
        $wechat_notification = $this->getDefaultWechatNotification();

        if (in_array($notification_type, ['serviceFollow', 'MessageSubscribe'])) {
            $wechat_notification['notification_type'] = $notification_type;
        }
        if ($notification_sms) {
            $wechat_notification['notification_sms'] = 1;
        }

        return $wechat_notification;
    }

    private function getDefaultWechatSetting()
    {
        return [
            'wechat_notification_enabled' => 0,
            'account_code' => '',
        ];
    }

    private function getDefaultWechatNotification()
    {
        return [
            'notification_type' => 'serviceFollow',
            'notification_sms' => 0,
        ];
    }

    private function getTemplateClient()
    {
        $biz = $this->getBiz();

        return $biz['wechat.template_message_client'];
    }

    private function getTemplateSetting($templates, $wechatSetting)
    {
        foreach ($templates as $key => &$template) {
            $template = empty($wechatSetting['templates'][$key]) ? $template : array_merge($template, $wechatSetting['templates'][$key]);
        }

        return $templates;
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

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->createService('Notification:NotificationService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
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
