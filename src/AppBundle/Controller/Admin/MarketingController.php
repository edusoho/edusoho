<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Biz\Marketing\Util\MarketingUtils;
use Biz\Marketing\MarketingAPIFactory;

class MarketingController extends BaseController
{
    public function loginMarketingAction(Request $request)
    {
        $merchantUrl = $request->getSchemeAndHttpHost();
        $user = $this->getCurrentUser();

        $client = MarketingAPIFactory::create();

        $siteInfo = MarketingUtils::getSiteInfo($this->getSettingService(), $this->getWebExtension());
        $entry = $request->query->get('entry');
        $target = $request->query->get('target', 'login');

        try {
            $login = $client->post('/login', array(
                'site' => $siteInfo,
                'url' => $merchantUrl,
                'user_id' => $user['id'],
                'user_name' => $user['nickname'],
                'user_avatar' => $this->getWebExtension()->getFurl($user['largeAvatar'], 'avatar.png'),
                'entry' => $entry,
            ));
            // 返回的login链接格式固定是：{微营销域名}/login?key=xxxx
            return  $this->redirect($login['url'].'&target='.$target);
        } catch (\Exception $e) {
            return $this->createMessageResponse('error', $e->getMessage());
        }
    }

    public function loginDistributorAction(Request $request)
    {
        $form = MarketingUtils::generateLoginFormForCurrentUser(array(
            'settingService' => $this->getSettingService(),
            'webExtension' => $this->getWebExtension(),
            'request' => $request,
            'currentUser' => $this->getCurrentUser(),
            'drpService' => $this->getDistributorUserService()->getDrpService(),
        ));

        return $this->render('admin/marketing/login.html.twig', array(
            'form' => $form,
        ));
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getDistributorUserService()
    {
        return $this->createService('Distributor:DistributorUserService');
    }
}
