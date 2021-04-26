<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Notification\Service\NotificationService;
use Biz\System\Service\SettingService;
use Biz\WeChat\Service\WeChatService;
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
        $wechatAuth = $this->getAuthorizationInfo();
        if ($wechatAuth['isAuthorized']) {
            $wechatNotificationSetting['is_authorization'] = 1;
        }

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
        $notification_type = $request->query->get('notification_type');
        if (!in_array($notification_type, ['serviceFollow', 'MessageSubscribe'])) {
            throw new \InvalidArgumentException('Notification type error');
        }
        $wechatSetting = $this->getSettingService()->get('wechat', []);
        $templates = $this->get('extension.manager')->getWeChatTemplates();
        $templates = $this->getTemplateSetting($templates, $wechatSetting);

        if ('MessageSubscribe' == $notification_type) {
            $templates = $this->get('extension.manager')->getMessageSubscribeTemplates();
            $templates = $this->getTemplateSetting($templates, $wechatSetting);
        }

        if ('POST' == $request->getMethod()) {
            if (empty($wechatSetting['wechat_notification_enabled'])) {
                throw new \RuntimeException($this->trans('wechat.notification.service_not_open'));
            }
            if (!$this->templateSettingFilter($notification_type, $templates[$key])) {
                return $this->createJsonResponse(true);
            }
            $fields = $request->request->all();
            if (1 == $fields['status']) {
                $this->getWeChatService()->addTemplate($templates[$key], $key, $notification_type);
            } else {
                $this->getWeChatService()->deleteTemplate($templates[$key], $key, $notification_type);
            }
            $this->getWeChatService()->saveWeChatTemplateSetting($key, $fields, $notification_type);

            return $this->createJsonResponse(true);
        }
        $modal = isset($templates[$key]['setting_modal_v2']) ? $templates[$key]['setting_modal_v2'] : 'admin-v2/operating/wechat-notification/setting-modal/default-modal.html.twig';

        return $this->render($modal, [
            'template' => $templates[$key],
            'wechatSetting' => $wechatSetting,
            'key' => $key,
            'notification_type' => $notification_type,
        ]);
    }

    public function templateSettingFilter($notification_type, $template)
    {
        if ('MessageSubscribe' == $notification_type) {
            return isset($template['id']);
        }

        return true;
    }

    public function settingNotificationAction(Request $request)
    {
        $notification_type = $request->request->get('notification_type');
        $notification_sms = $request->request->get('notification_sms');
        $wechat_notification_config = $this->prepareWechatNotificationSetting($notification_type, $notification_sms);
        $this->getSettingService()->set('wechat_notification', $wechat_notification_config);

        return $this->createJsonResponse(true);
    }

    private function prepareWechatNotificationSetting($notification_type, $notification_sms)
    {
        $wechatSetting = array_merge($this->getDefaultWechatNotificationSetting(), $this->getSettingService()->get('wechat_notification', []));

        if (in_array($notification_type, ['serviceFollow', 'MessageSubscribe'])) {
            $wechatSetting['notification_type'] = $notification_type;
        }
        if ($notification_sms) {
            $wechatSetting['notification_sms'] = 1;
        }
        if ('serviceFollow' == $notification_type) {
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

    protected function getAuthorizationInfo()
    {
        $biz = $this->getBiz();
        try {
            $info = $biz['qiQiuYunSdk.wechat']->getAuthorizationInfo(WeChatPlatformTypes::OFFICIAL_ACCOUNT);
            if ($info['isAuthorized']) {
                $ids = ArrayToolkit::column($info['funcInfo'], 'funcscope_category');
                $ids = ArrayToolkit::column($ids, 'id');
                /**
                 * 2、用户管理权限  7、群发与通知权限
                 */
                $needIds = [2, 7];
                $diff = array_diff($needIds, $ids);
                if (empty($diff)) {
                    $info['wholeness'] = 1;
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

        return $biz['qiQiuYunSdk.wechat'];
    }
}
