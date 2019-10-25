<?php

namespace AppBundle\Controller\AdminV2\Marketing;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\Marketing\MarketingAPIFactory;
use Biz\Marketing\Util\MarketingUtils;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class MicromarketingController extends BaseController
{
    public function loginAction(Request $request)
    {
        $storage = $this->getSettingService()->get('storage');
        if (empty($storage['cloud_access_key']) || empty($storage['cloud_secret_key'])) {
            return $this->render('admin-v2/marketing/micromarketing/not-access.html.twig', array());
        }

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
            return $this->redirect($login['url'].'&target='.$target);
        } catch (\Exception $e) {
            return $this->createMessageResponse('error', $e->getMessage());
        }
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
