<?php

namespace Biz\S2B2C\Service\Impl;

use Biz\BaseService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\S2B2C\Service\SupplierNotifyService;
use Biz\System\Service\SettingService;
use Biz\Util\FileUtil;
use Biz\Util\SystemUtil;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Topxia\Service\Common\ServiceKernel;

class SupplierNotifyServiceImpl extends BaseService implements SupplierNotifyService
{
    public function onSiteStatusChange($params)
    {
        return null;
    }

    public function onCoopModeChange($params)
    {
        $this->getLogger()->info('[onCoopModeChange]通知参数', $params);
        $new = $this->getS2B2CServiceApi()->getMe();
        if (!isset($new['coop_mode'])) {
            $this->getLogger()->error('[onCoopModeChange] 获取渠道商信息失败，停止处理[DATA]：'.json_encode($new));

            return ['status' => false];
        }

        $config = $this->getParameters();
        if (isset($config['parameters']['school_mode']['business_mode'])) {
            $config['parameters']['school_mode']['business_mode'] = $new['coop_mode'];
            $content = $this->dumpParameters($config);
            $this->backupParameters();
            $this->writeParameters($content);
        }
        $this->getLogger()->info("[onCoopModeChange] 更新渠道商#{$new['name']}合作模式#{$new['coop_mode']}成功");
        $this->getS2B2CFacadeService()->updateMerchantDisabledPermissions();
        FileUtil::emptyDir(SystemUtil::getCachePath());

        return ['success' => true];
    }

    /**
     * @param $params
     */
    public function onMerchantDomainUrlChange($params)
    {
        return null;
    }

    public function onSupplierDomainUrlChange($params)
    {
        $this->getLogger()->info('[onSupplierDomainUrlChange]通知参数', $params);
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

    public function onSupplierSiteLogoAndFaviconChange($params)
    {
        $this->getLogger()->info('[onSupplierSiteLogoChange]通知参数', $params);
        $site = $this->getSettingService()->get('site');
        $permissions = $this->getS2B2CFacadeService()->getBehaviourPermissions();

        if (!$permissions['canModifySiteLogo']) {
            $site['logo'] = $params['logo'];
        }

        if (!$permissions['canModifySiteFavicon']) {
            $site['favicon'] = $params['favicon'];
        }

        $this->getSettingService()->set('site', $site);

        return ['success' => true];
    }

    public function onMerchantAuthNodeChange($params)
    {
        $this->getLogger()->info('[onMerchantAuthNodeChange]通知参数', $params);
        $me = $this->getS2B2CServiceApi()->getMe();
        $authNode = isset($me['auth_node']) ? $me['auth_node'] : [];
        $this->checkAuthNode($authNode);

        $setting = $this->getSettingService()->get('s2b2c', []);
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
        $this->getLogger()->info('[onResetMerchantBrand]通知参数', $params);
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
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
        $parseUrl = parse_url($s2b2cConfig['supplierDomain']);
        $host = empty($parseUrl['host']) ? $parseUrl['path'] : $parseUrl['host'];

        $logo = [
            'logo' => sprintf('%s/logo/%s_logo.png', $s2b2cConfig['supplierDomain'], $host),
        ];

        $site = $this->getSettingService()->get('site', []);
        $site = array_merge($site, $logo);
        $this->getSettingService()->set('site', $site);

        return true;
    }

    protected function resetSiteFavicon()
    {
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
        $parseUrl = parse_url($s2b2cConfig['supplierDomain']);
        $host = empty($parseUrl['host']) ? $parseUrl['path'] : $parseUrl['host'];

        $favicon = [
            'favicon' => sprintf('%s/favicon/%s_favicon.ico', $s2b2cConfig['supplierDomain'], $host),
        ];
        $site = $this->getSettingService()->get('site', []);
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
            $this->getSettingService()->get('site', []),
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
