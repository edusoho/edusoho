<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Component\Notification\WeChatTemplateMessage\TemplateUtil;

class WeChatNotificationController extends BaseController
{
    public function recordAction(Request $request)
    {
        return $this->render('admin/wechat-notification/index.html.twig', array(
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
            throw new \RuntimeException('未开启微信登录');
        }

        $data = $clinet->addTemplate($template['id']);

        if (empty($data)) {
            throw new \RuntimeException('模板操作失败');
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
            throw new \RuntimeException('未开启微信登录');
        }

        $wechatSetting = $this->getSettingService()->get('wechat');

        if (empty($wechatSetting[$key]['templateId'])) {
            throw new \RuntimeException('模版不存在');
        }

        $data = $clinet->deleteTemplate($wechatSetting[$key]['templateId']);

        if (empty($data)) {
            throw new \RuntimeException('模板操作失败');
        }

        $wechatSetting[$key]['templateId'] = '';
        $wechatSetting[$key]['status'] = 0;

        return $this->getSettingService()->set('wechat', $wechatSetting);
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
            'account_name' => '',
            'account_code' => '',
        );
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
