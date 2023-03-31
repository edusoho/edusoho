<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Notification\Service\NotificationService;
use Biz\System\Service\SettingService;
use Biz\WeChat\Service\WeChatService;
use Biz\WeChat\WechatNotificationType;
use QiQiuYun\SDK\Constants\NotificationChannelTypes;
use QiQiuYun\SDK\Constants\WeChatPlatformTypes;
use Symfony\Component\HttpFoundation\Request;

class WeChatNotificationController extends BaseController
{
    public function manageAction(Request $request)
    {
        $wechatDefault = $this->getDefaultWechatSetting();
        $wechatSetting = $this->getSettingService()->get('wechat', []);
        $wechatSetting = array_merge($wechatDefault, $wechatSetting);

        $wechatNotificationDefault = $this->getDefaultWechatNotificationSetting();
        $wechatNotificationSetting = $this->getSettingService()->get('wechat_notification');
        $wechatNotificationSetting = array_merge($wechatNotificationDefault, $wechatNotificationSetting);

        $wechatAuth = $this->getAuthorizationInfo($wechatNotificationSetting);
        $wechatNotificationSetting['is_authorization'] = $wechatAuth['isAuthorized'];

        $templates = $this->get('extension.manager')->getWeChatTemplates();
        $templates = $this->getTemplateSetting($templates, $wechatSetting);
        $messageSubscribeTemplates = $this->get('extension.manager')->getMessageSubscribeTemplates();
        $messageSubscribeTemplates = $this->getTemplateSetting($messageSubscribeTemplates, $wechatNotificationSetting);

        $this->getSettingService()->set('wechat_notification', $wechatNotificationSetting);

        return $this->render('admin-v2/operating/wechat-notification/manage.html.twig', [
            'wechatSetting' => $wechatSetting,
            'wechatNotificationSetting' => $wechatNotificationSetting,
            'messageSubscribeTemplates' => $messageSubscribeTemplates,
            'templates' => $templates,
            'isCloudOpen' => $this->isCloudOpen(),
        ]);
    }

    public function indexAction(Request $request)
    {
        $notificationMode = $this->getSettingService()->get('wechat_notification', []);
        $mode = empty($notificationMode) || 'messageSubscribe' != $notificationMode['notification_type'] ? 'wechat_template' : 'wechat_subscribe';
        $conditions = [
            'source' => $mode,
        ];
        $paginator = new Paginator(
            $request,
            $this->getNotificationService()->countBatches($conditions),
            20
        );
        $notifications = $this->getNotificationService()->searchBatches(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $notifications = ArrayToolkit::index($notifications, 'id');
        $notificationIds = ArrayToolkit::column($notifications, 'eventId');
        $this->getNotificationService()->batchHandleNotificationResults($notifications);
        $notificationEvents = $this->getNotificationService()->findEventsByIds($notificationIds);
        $notificationEvents = ArrayToolkit::index($notificationEvents, 'id');
        $smsNotificationEvents = $this->getNotificationService()->findEventsByIds(array_column($notifications, 'smsEventId'));
        $smsNotificationEvents = ArrayToolkit::index($smsNotificationEvents, 'id');

        if ('wechat_template' == $mode) {
            return $this->render('admin-v2/operating/wechat-notification/index.html.twig', [
                'notifications' => $notifications,
                'notificationEvents' => $notificationEvents,
                'smsNotificationEvents' => $smsNotificationEvents,
                'paginator' => $paginator,
            ]);
        }

        return $this->render('admin-v2/operating/wechat-notification/subscribe-record.html.twig', [
            'notifications' => $notifications,
            'notificationEvents' => $notificationEvents,
            'smsNotificationEvents' => $smsNotificationEvents,
            'paginator' => $paginator,
        ]);
    }

    public function recordDetailAction(Request $request, $id)
    {
        $notificationMode = $this->getSettingService()->get('wechat_notification', []);
        $mode = empty($notificationMode) || 'messageSubscribe' != $notificationMode['notification_type'] ? 'wechat_template' : 'wechat_subscribe';
        $notification = $this->getNotificationService()->getEvent($id);

        return $this->render('admin-v2/operating/wechat-notification/notification-modal.html.twig', [
            'notification' => $notification,
            'mode' => $mode,
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
        $notificationType = $request->query->get('notificationType');
        if (!in_array($notificationType, ['serviceFollow', 'messageSubscribe'])) {
            throw new \InvalidArgumentException('Notification type error');
        }
        $wechatSetting = $this->getSettingService()->get('wechat', []);
        $templates = $this->get('extension.manager')->getWeChatTemplates();
        $templates = $this->getTemplateSetting($templates, $wechatSetting);
        $wechat_notification_enabled = $wechatSetting['wechat_notification_enabled'];

        if (WechatNotificationType::MESSAGE_SUBSCRIBE == $notificationType) {
            $wechatSetting = $this->getSettingService()->get('wechat_notification', []);
            $templates = $this->get('extension.manager')->getMessageSubscribeTemplates();
            $templates = $this->getTemplateSetting($templates, $wechatSetting);
        }

        if ('POST' == $request->getMethod()) {
            if (empty($wechat_notification_enabled)) {
                throw new \RuntimeException($this->trans('wechat.notification.service_not_open'));
            }
            $fields = $request->request->all();

            if ($this->templateSettingFilter($notificationType, $templates[$key])) {
                if (1 == $fields['status']) {
                    $this->getWeChatService()->addTemplate($templates[$key], $key, $notificationType);
                } else {
                    $this->getWeChatService()->deleteTemplate($templates[$key], $key, $notificationType);
                }
            }

            $this->getWeChatService()->saveWeChatTemplateSetting($key, $fields, $notificationType);

            return $this->createJsonResponse(true);
        }
        $modal = $templates[$key]['setting_modal_v2'] ?? 'admin-v2/operating/wechat-notification/setting-modal/default-modal.html.twig';

        return $this->render($modal, [
            'template' => $templates[$key],
            'wechatSetting' => $wechatSetting,
            'key' => $key,
            'notificationType' => $notificationType,
        ]);
    }

    public function templateSettingFilter($notificationType, $template)
    {
        if (WechatNotificationType::MESSAGE_SUBSCRIBE == $notificationType) {
            return isset($template['id']);
        }

        return true;
    }

    public function settingNotificationAction(Request $request)
    {
        $notificationType = $request->request->get('notificationType');
        $notificationSms = $request->request->get('notificationSms');
        $wechat_notification_config = $this->prepareWechatNotificationSetting($notificationType, $notificationSms);
        $this->getSettingService()->set('wechat_notification', $wechat_notification_config);
        $setting = $this->getSettingService()->get('wechat_notification');
        if (WechatNotificationType::MESSAGE_SUBSCRIBE == $setting['notification_type']) {
            $loginConnect = $this->getSettingService()->get('login_bind');
            $this->getBiz()['ESCloudSdk.notification']->openChannel(NotificationChannelTypes::WECHAT_SUBSCRIBE, [
                'app_id' => $loginConnect['weixinmob_key'],
                'app_secret' => $loginConnect['weixinmob_secret'],
            ]);
        }

        return $this->createJsonResponse(true);
    }

    private function prepareWechatNotificationSetting($notificationType, $notificationSms)
    {
        $wechatSetting = array_merge($this->getDefaultWechatNotificationSetting(), $this->getSettingService()->get('wechat_notification', []));

        if (in_array($notificationType, ['serviceFollow', 'messageSubscribe'])) {
            $wechatSetting['notification_type'] = $notificationType;
        }
        if ($notificationSms) {
            $wechatSetting['notification_sms'] = 1;
        } else {
            $wechatSetting['notification_sms'] = 0;
        }
        if (WechatNotificationType::SERVICE_FOLLOW == $notificationType) {
            $wechatSetting['notification_sms'] = 0;
        }

        return $wechatSetting;
    }

    private function getDefaultWechatSetting()
    {
        return [
            'wechat_notification_enabled' => 0,
            'account_code' => '',
        ];
    }

    private function getDefaultWechatNotificationSetting()
    {
        return [
            'is_authorization' => 0,
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

    protected function getAuthorizationInfo($setting)
    {
        $mode = empty($setting) || 'messageSubscribe' != $setting['notification_type'] ? 'wechat_template' : 'wechat_subscribe';
        $biz = $this->getBiz();
        try {
            $info = $biz['ESCloudSdk.wechat']->getAuthorizationInfo(WeChatPlatformTypes::OFFICIAL_ACCOUNT);
            if ($info['isAuthorized']) {
                $ids = ArrayToolkit::column($info['funcInfo'], 'funcscope_category');
                $ids = ArrayToolkit::column($ids, 'id');
                /**
                 * 2、用户管理权限  7、群发与通知权限  89、订阅通知权限
                 */
                $needIds = 'wechat_template' == $mode ? [2, 7] : [2, 7, 89];
                $diff = array_diff($needIds, $ids);
                if (empty($diff)) {
                    $info['wholeness'] = 1;
                } else {
                    $info['isAuthorized'] = 'wechat_template' == $mode ? true : false;
                }
            }
        } catch (\Exception $e) {
            $info = [
                'isAuthorized' => false,
            ];
        }

        return $info;
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

        return $biz['ESCloudSdk.wechat'];
    }
}
