<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\System\Service\SettingService;
use Biz\WeChat\Service\WeChatAppService;
use Symfony\Component\HttpFoundation\Request;

class SettingController extends BaseController
{
    //微网校
    public function wapSetAction(Request $request)
    {
        $defaultWapSetting = [
            'version' => 1,
            'template' => 'jianmoOn',
        ];

        if ($request->isMethod('POST')) {
            $wapSetting = $request->request->all();
            $wapSetting = ArrayToolkit::parts($wapSetting, [
                'version', 'template',
            ]);

            $template = $wapSetting['template'];
            $wapSetting = array_merge($defaultWapSetting, $wapSetting);
            $this->getSettingService()->set('wap', $wapSetting);
            $result = CloudAPIFactory::create('leaf')->get('/me');
            if (empty($result['error'])) {
                $this->getSettingService()->set('meCount', $result);
            }
        }

        $wapSetting = $this->setting('wap', []);
        $wapSetting = array_merge($defaultWapSetting, $wapSetting);

        return $this->render('admin-v2/operating/wap/set.html.twig', [
            'wapSetting' => $wapSetting,
            'template' => empty($template) ? '' : $template,
            'currentTheme' => $this->get('web.twig.extension')->getSetting('theme'),
        ]);
    }

    //小程序
    public function weChatAppAction()
    {
        $wechatAppStatus = $this->getWeChatAppService()->getWeChatAppStatus();

        return $this->render('admin-v2/operating/wechat-app/index.html.twig', $wechatAppStatus);
    }

    /**
     * @return WeChatAppService
     */
    protected function getWeChatAppService()
    {
        return $this->createService('WeChat:WeChatAppService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
