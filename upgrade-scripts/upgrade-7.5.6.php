<?php

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->batchUpdate();
            $this->getConnection()->commit();
            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set("crontab_next_executed_time", time());
    }

    protected function batchUpdate($index)
    {
        $this->updateScheme();
        $this->updateAlipayType();
        $this->cloudSearchEnable();
    }

    protected function updateScheme()
    {
        $connection = $this->getConnection();

        $connection->exec("
            ALTER TABLE `classroom` ADD `expiryMode` enum('date', 'days', 'none') NOT NULL DEFAULT 'none' COMMENT '有效期的模式'; 
        ");

        $connection->exec("
            ALTER TABLE `classroom` ADD `expiryValue` int(10) NOT NULL DEFAULT '0' COMMENT '有效期'; 
        ");

        $connection->exec("
            ALTER TABLE `classroom_member` ADD `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '到期时间'; 
        ");

        $connection->exec("
            ALTER TABLE `classroom_member` ADD `deadlineNotified` int(10) NOT NULL DEFAULT '0' COMMENT '有效期通知'; 
        ");
    }

    protected function updateAlipayType()
    {
        $payment = $this->getSettingService()->get('payment', array());
        if ($payment) {
            $payment['alipay_type'] = 'direct';
            $this->getSettingService()->set('payment', $payment);
        }
    }

    protected function cloudSearchEnable()
    {
        $api  = CloudAPIFactory::create('root');
        $info = $api->get('/me');
        $searchOverview = $api->get("/me/search/overview");
        $status = $this->canOpenCloudSearch($searchOverview);
        if ($status) {
            $this->dealSaaSCloudSearch($info, $searchOverview);
        }
    }

    private function dealSaaSCloudSearch($info, $searchOverview)
    {
        $eduCloudType = array('medium', 'personal', 'basic', 'advanced', 'gold');
        if (in_array($info['level'], $eduCloudType) && !isset($searchOverview['isBuy'])) {
            $searchSetting = $this->getSettingService()->get('cloud_search', array());
            if ($searchSetting['status'] == 'closed') {
                $this->cloudSearchClause();
            } elseif (empty($searchSetting['search_enabled'])) {
                $this->cloudSearchOpen($searchSetting);
            }  else {
                $this->cloudSearchClause();
            }
        }
    }

    private function cloudSearchClause()
    {
        $callbackRouteUrl = '/edu_cloud/search/callback';
        $this->getSearchService()->applySearchAccount($callbackRouteUrl);
    }

    private function cloudSearchOpen($searchSetting)
    {
        if ($searchSetting['status'] == 'ok' || $searchSetting['status'] == 'waiting') {
            $this->getSettingService()->set('cloud_search', array(
                'search_enabled' => 1,
                'status'         => $searchSetting['status'],
                'type'           => array(
                    'course'     => 1,
                    'teacher'    => 1,
                    'thread'     => 1, 
                    'article'    => 1
                )
            ));
        }
    }

    private function canOpenCloudSearch($searchOverview)
    {
        try {
            $api = CloudAPIFactory::create('root');

            $searchSetting = $this->getSettingService()->get('cloud_search', array());
            $userOverview = $api->get("/users/{$api->getAccessKey()}/overview");
            $data = $this->isSearchInited($api, $searchSetting);
        } catch (\RuntimeException $e) {
        }

        //判断云搜索状态
        if (empty($userOverview['user']['licenseDomains'])) {
            $data['status'] = 'unbinded';
            return false;
        } else {
            $site = $this->getSettingService()->get('site');
            // $currentHost = $_SERVER['HTTP_HOST'];
            if (!in_array($site['url'], explode(';', $userOverview['user']['licenseDomains']))) {
                $data['status'] = 'binded_error';
                return false;
            }
        }
        return true;
    }

    protected function isSearchInited($api, $data)
    {
        if (!$data) {
            $data = array(
                'search_enabled' => 0,
                'status'         => 'closed' //'closed':未开启；'waiting':'索引中';'ok':'索引完成'
            );
        }

        if ($data['status'] == 'waiting') {
            $search_account = $api->get("/me/search_account");

            if ($search_account['isInit'] == 'yes') {
                $data = array(
                    'search_enabled' => $data['search_enabled'],
                    'status'         => 'ok'
                );
            }
        }
        $this->getSettingService()->set('cloud_search', $data);

        return $data;
    }

    protected function getSearchService()
    {
        return ServiceKernel::instance()->createService('Search.SearchService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}

//抽象类
abstract class AbstractUpdater
{
    protected $kernel;
    public function __construct ($kernel)
    {
        $this->kernel = $kernel;
    }

    public function getConnection()
    {
        return $this->kernel->getConnection();
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    abstract public function update();

}