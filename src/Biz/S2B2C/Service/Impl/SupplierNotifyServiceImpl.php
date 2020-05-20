<?php

namespace Biz\S2B2C\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\S2B2C\Service\SupplierNotifyService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Topxia\Service\Common\ServiceKernel;

class SupplierNotifyServiceImpl extends BaseService implements SupplierNotifyService
{
    public function onSiteStatusChange($params)
    {
        if (!ArrayToolkit::requireds($params, ['site_id', 'site_status'])) {
            throw new \InvalidArgumentException('Params Missing');
        }

        $this->getDatabaseConfigService()->updateSiteStatusBySiteId($params['site_id'], $params['site_status']);

        return ['success' => true];
    }

    public function onCoopModeChange($params)
    {
        $new = $this->getS2B2CServiceApi()->getMe();
        if (!isset($new['coop_mode'])) {
            $this->getLogger()->error('[onCoopModeChange] 获取渠道商信息失败，停止处理[DATA]：'.json_encode($new));

            return ['status' => false];
        }

        $config = $this->getParameters();
        if (isset($config['parameters']['school_mode']['business_mode'])) {
            $config['parameters']['school_mode']['business_mode'] = $new['coop_mode'];
            $content = $this->dumpParameters($config);
            $this->writeParameters($content);
        }
        $this->getLogger()->info("[onCoopModeChange] 更新渠道商#{$new['name']}合作模式#{$new['coop_mode']}成功");

        return ['success' => true];
    }

    /**
     * @param $params
     *
     * @return null
     *              EduSoho 不存在更新B端URL
     */
    public function onMerchantDomainUrlChange($params)
    {
        return null;
    }

    public function onSupplierDomainUrlChange($params)
    {
        $config = $this->getParameters();
        if (isset($config['parameters']['school_mode']['supplier']['domain'])) {
            $config['parameters']['school_mode']['supplier']['domain'] = $params['domain_url'];
            $content = $this->dumpParameters($config);
            $this->backupParameters();
            $this->writeParameters($content);
            $this->getLogger()->info("[onCoopModeChange] 更新渠道商URL成功:{$params['domain_url']}");
        }

        return ['success' => true];
    }

    public function onSupplierSiteLogoChange($params)
    {
        $site = $this->getSettingService()->get('site');

        if (!empty($site['logo'])) {
            $logoData = explode('?', $site['logo']);
            $site['logo'] = $logoData[0].'?'.time();
        }

        if (!empty($site['favicon'])) {
            $faviconData = explode('?', $site['favicon']);
            $site['favicon'] = $faviconData[0].'?'.time();
        }

        $this->getSettingService()->set('site', $site);

        return ['success' => true];
    }

    public function onMerchantAuthNodeChange($params)
    {
        $me = $this->getS2B2CServiceApi()->getMe();
        $authNode = isset($me['auth_node']) ? $me['auth_node'] : [];
        $this->checkAuthNode($authNode);

        $setting = $this->getSettingService()->get('s2b2c');
        $setting['auth_node'] = [
            'logo' => $me['auth_node']['logo'],
            'title' => $me['auth_node']['title'],
            'favicon' => $me['auth_node']['favicon'],
        ];
        $this->getSettingService()->set('s2b2c', $setting);

        return ['success' => true];
    }

    public function onResetMerchantBrand($params)
    {
        $this->resetSiteTitle();
        $this->resetSiteLogo();
        $this->resetSiteFavicon();

        return ['success' => true];
    }

    protected function dumpParameters($config)
    {
        $yaml = new Yaml();

        return $yaml->dump($config, 4);
    }

    protected function getParameters()
    {
        $filePath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/config/parameters.yml';

        $yaml = new Yaml();

        return $yaml->parseFile($filePath);
    }

    protected function writeParameters($content)
    {
        $filePath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/config/parameters.yml';
        $fh = fopen($filePath, 'w');
        fwrite($fh, $content);
        fclose($fh);
    }

    protected function backupParameters()
    {
        $filePath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/config/parameters.yml';
        $time = time();
        $backupPath = ServiceKernel::instance()->getParameter('kernel.root_dir')."/data/backup/parameter_{$time}.yml.bak";
        $fileSystem = new Filesystem();
        if ($fileSystem->exists($filePath)) {
            $fileSystem->copy($filePath, $backupPath, true);
        }
    }

    protected function resetSiteLogo()
    {
        $setting = $this->getSettingService()->get('supplierSettings', ['domainUrl' => '']);
        $parseUrl = parse_url($setting['domainUrl']);
        $host = $parseUrl['host'];

        $logo = [
            'logo' => sprintf('%s/logo/%s_logo.png', $setting['domainUrl'], $host),
        ];

        $site = $this->getSettingService()->get('site');
        $site = array_merge($site, $logo);
        $this->getSettingService()->set('site', $site);

        return true;
    }

    protected function resetSiteFavicon()
    {
        $setting = $this->getSettingService()->get('supplierSettings', ['domainUrl' => '']);
        $parseUrl = parse_url($setting['domainUrl']);
        $host = $parseUrl['host'];

        $favicon = [
            'favicon' => sprintf('%s/favicon/%s_favicon.ico', $setting['domainUrl'], $host),
        ];
        $site = $this->getSettingService()->get('site');
        $site = array_merge($site, $favicon);
        $this->getSettingService()->set('site', $site);
    }

    protected function resetSiteTitle()
    {
        $merchant = $this->getS2B2CServiceApi()->getMe();
        if (!$merchant['site_title']) {
            throw new ServiceException('resetSiteTitle failed: '.json_encode($merchant));
        }

        $site = array_merge(
            $this->getSettingService()->get('site'),
            ['name' => $merchant['site_title']]
        );

        $this->getSettingService()->set('site', $site);
    }

    protected function resetSiteUrl()
    {
        $merchant = $this->getS2B2CServiceApi()->getMe();
        if (!$merchant['domain_url']) {
            throw new ServiceException('resetSiteTitle failed: '.json_encode($merchant));
        }

        $site = array_merge(
            $this->getSettingService()->get('site'),
            ['url' => $merchant['domain_url']]
        );

        $this->getSettingService()->set('site', $site);
    }

    protected function checkAuthNode($authNode)
    {
        $whole = isset($authNode['logo']) &&
                 isset($authNode['title']) &&
                 isset($authNode['favicon']);

        if (!$whole) {
            $this->getLogger()->error('[onMerchantAuthNodeChange] 获取渠道商权限节点失败，停止处理[DATA]：'.json_encode($authNode));
        }
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return \QiQiuYun\SDK\Service\S2B2CService
     */
    protected function getS2B2CServiceApi()
    {
        return $this->getS2B2CFacadeService()->getS2B2CService();
    }

    /**
     * @return mixed|\Monolog\Logger
     */
    protected function getLogger()
    {
        return $this->biz->offsetGet('s2b2c.merchant.logger');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }
}
