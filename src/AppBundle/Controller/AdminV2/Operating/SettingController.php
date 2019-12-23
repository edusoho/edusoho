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
        $defaultWapSetting = array(
            'version' => 1,
        );

        if ($request->isMethod('POST')) {
            $wapSetting = $request->request->all();
            $wapSetting = ArrayToolkit::parts($wapSetting, array(
                'version',
            ));

            $wapSetting = array_merge($defaultWapSetting, $wapSetting);
            $this->getSettingService()->set('wap', $wapSetting);
            $result = CloudAPIFactory::create('leaf')->get('/me');
            if (empty($result['error'])) {
                $this->getSettingService()->set('meCount', $result);
            }
            $this->setFlashMessage('success', 'site.save.success');
        }

        $wapSetting = $this->setting('wap', array());
        $wapSetting = array_merge($defaultWapSetting, $wapSetting);

        return $this->render('admin-v2/operating/wap/set.html.twig', array(
            'wapSetting' => $wapSetting,
        ));
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
