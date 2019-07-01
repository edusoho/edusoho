<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Notification\Service\NotificationService;
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

    public function manageAction(Request $request)
    {
        $wechatDefault = $this->getDafaultWechatSetting();
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

    public function statusAction(Request $request)
    {
        $isEnable = $request->request->get('isEnable');
        $key = $request->query->get('key');
        $templates = TemplateUtil::templates();
        $template = $templates[$key];
        $wechatSetting = $this->getSettingService()->get('wechat', array());
        if (empty($wechatSetting['wechat_notification_enabled'])) {
            throw new \RuntimeException($this->trans('wechat.notification.service_not_open'));
        }

        if ($isEnable) {
            $this->addTemplate($template, $key);
        } else {
            $this->deleteTemplate($template, $key);
        }

        return $this->createJsonResponse(true);
    }

    protected function addTemplate($template, $key)
    {
        $clinet = $this->getTemplateClient();
        if (empty($clinet)) {
            throw new \RuntimeException($this->trans('wechat.notification.empty_token'));
        }

        $data = $clinet->addTemplate($template['id']);

        if (empty($data)) {
            throw new \RuntimeException($this->trans('wechat.notification.template_open_error'));
        }

        $wechatSetting = $this->getSettingService()->get('wechat');
        $wechatSetting[$key]['templateId'] = $data['template_id'];
        $wechatSetting[$key]['status'] = 1;

        return $this->getSettingService()->set('wechat', $wechatSetting);
    }

    protected function deleteTemplate($template, $key)
    {
        $clinet = $this->getTemplateClient();
        if (empty($clinet)) {
            throw new \RuntimeException($this->trans('wechat.notification.empty_token'));
        }

        $wechatSetting = $this->getSettingService()->get('wechat');

        if (empty($wechatSetting[$key]['templateId'])) {
            throw new \RuntimeException($this->trans('wechat.notification.template_not_exist'));
        }

        $data = $clinet->deleteTemplate($wechatSetting[$key]['templateId']);

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

    private function getDafaultWechatSetting()
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
}
