<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Codeages\RestApiClient\RestApiClient;
use Codeages\RestApiClient\Specification\JsonHmacSpecification2;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use QiQiuYun\SDK\Auth;

class MarketingController extends BaseController
{
    public function toMarketingAction(Request $request)
    {
        $merchantUrl = $request->getSchemeAndHttpHost();
        $user = $this->getCurrentUser();

        $client = $this->createMarketingClient();

        $siteInfo = $this->getSiteInfo();
        $entry = $request->query->get('entry');

        try {
            $login = $client->post('/login', array(
                'site' => $siteInfo,
                'url' => $merchantUrl,
                'user_id' => $user['id'],
                'user_name' => $user['nickname'],
                'user_avatar' => $this->getWebExtension()->getFurl($user['largeAvatar'], 'avatar.png'),
                'entry' => $entry,
            ));

            return  $this->redirect($login['url']);
        } catch (\Exception $e) {
            return $this->createMessageResponse('error', $e->getMessage());
        }
    }

    public function loginMarketingAction(Request $request)
    {
        $marketingEntry = $request->get('entry', '');
        if (empty($marketingEntry)) {
            throw new InvalidArgumentException('entry is require');
        }
        $marketingEntry = strtolower($marketingEntry);
        // $loginDomain = $this->getMarketingDomain($marketingEntry);
        // $loginPath = $this->getMarketingPath($marketingEntry);
        $site = $this->getSiteInfo();
        $siteDomain = $request->getSchemeAndHttpHost();
        $site['domain'] = $siteDomain;

        $storage = $this->getSettingService()->get('storage', array());
        $site['access_key'] = $storage['cloud_access_key'];

        $user = $this->getCurrentUser();
        $user = ['user_source_id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->getFurl($user['largeAvatar'], 'avatar.png'),
        ];

        // $spec = new JsonHmacSpecification2('sha1');
        // $body = $spec->serialize(['user' => $user, 'site' => $site]);

        // $once = $this->makeRequestId();
        // $deadline = time() + 5 * 60;
        // $sign = $spec->signature(['secretKey' => $storage['cloud_secret_key']], $loginPath, $body, $deadline, $once);
        // $sign = "{$storage['cloud_access_key']}:{$deadline}:{$once}:{$sign}";
        $loginDomain = $this->getMarketingDomain($marketingEntry);
        $loginPath = $this->getMarketingPath($marketingEntry);
        $baseURI = $loginDomain.$loginPath;
        $form = $this->createMarketingService($baseURI)->generateLoginForm($user, $site);

        return $this->render('admin/marketing/login.html.twig', array(
            'form' => $form,
        ));
    }

    public function createMarketingService($baseURI)
    {
        $settings = $this->getSettingService()->get('storage', array());
        $siteSettings = $this->getSettingService()->get('site', array());

        $siteName = empty($siteSettings['name']) ? '' : $siteSettings['name'];
        $siteUrl = empty($siteSettings['url']) ? '' : $siteSettings['url'];
        $accessKey = empty($settings['cloud_access_key']) ? '' : $settings['cloud_access_key'];
        $secretKey = empty($settings['cloud_secret_key']) ? '' : $settings['cloud_secret_key'];
        $auth = new Auth($accessKey, $secretKey);

        return new \QiQiuYun\SDK\Service\MarketingService($auth, array(
            'base_uri' => $baseURI, //推送的URL需要配置
        ));
    }

    private function makeRequestId()
    {
        return ((string) (microtime(true) * 10000)).substr(md5(uniqid('', true)), -18);
    }

    private function getMarketingDomain($entry)
    {
        $developerSetting = $this->getSettingService()->get('developer', array());
        $entryDomainKey = 'marketing_'.$entry.'_domain';
        $defaultDomain = "http://{$entry}.marketing.com";
        $domain = (isset($developerSetting[$entryDomainKey]) && !empty($developerSetting[$entryDomainKey]))
            ? $developerSetting[$entryDomainKey] : $defaultDomain;

        return $domain;
    }

    private function getMarketingPath($entry)
    {
        if ($entry == 'wyx') {
            $path = '/merchant/newlogin';
        }
        $path = '/merchant/login';

        return $path;
    }

    private function createMarketingClient()
    {
        $storage = $this->getSettingService()->get('storage', array());
        $developerSetting = $this->getSettingService()->get('developer', array());

        $marketingDomain = (
                            isset($developerSetting['marketing_domain']) &&
                            !empty($developerSetting['marketing_domain'])
                        ) ? $developerSetting['marketing_domain'] : 'http://wyx.edusoho.cn';

        $config = array(
            'accessKey' => $storage['cloud_access_key'],
            'secretKey' => $storage['cloud_secret_key'],
            'endpoint' => $marketingDomain.'/merchant',
        );
        $spec = new JsonHmacSpecification2('sha1');
        $client = new RestApiClient($config, $spec);

        return $client;
    }

    private function getSiteInfo()
    {
        $site = $this->getSettingService()->get('site', array());

        $site['logo'] = preg_replace('#files/#', '', $site['logo'], 1);
        $consult = $this->getSettingService()->get('consult', array());
        $wechatFile = isset($consult['webchatURI']) ? $consult['webchatURI'] : '';
        $consult['webchatURI'] = preg_replace('#files/#', '', $wechatFile, 1);

        $siteInfo = array(
            'name' => $site['name'],
            'logo' => empty($site['logo']) ? '' : $this->getWebExtension()->getFurl($site['logo']),
            'about' => $site['slogan'],
            'wechat' => empty($consult['webchatURI']) ? '' : $this->getWebExtension()->getFurl($consult['webchatURI']),
            'qq' => empty($consult['qq']) ? '' : $consult['qq'][0]['number'],
            'telephone' => empty($consult['phone']) ? '' : $consult['phone'][0]['number'],
        );

        return $siteInfo;
    }

    public function canOpenMarketing(Request $request)
    {
        return true;
    }

    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
