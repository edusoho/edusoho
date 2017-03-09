<?php

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Topxia\Common\ArrayToolkit;

class EduSohoUpgrade extends AbstractUpdater
{
    private static $pageNum = 2000;

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->batchUpdate($index);
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

    protected function generateIndex($step, $page)
    {
        return $step * 1000000 + $page;
    }

    protected function getStepAndPage($index)
    {
        $step = intval($index / 1000000);
        $page = $index % 1000000;
        return array($step, $page);
    }

    protected function batchUpdate($index)
    {
        $batchUpdates = array(
            1 => 'updateAlipayType',
            2 => 'cloudSearchEnable'
        );
        if ($index == 0) {
            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '正在升级数据...',
                'progress' => 0
            );
        }

        list($step, $page) = $this->getStepAndPage($index);
        $method = $batchUpdates[$step];
        $page = $this->$method($page);

        if ($page == 1) {
            $step ++;
        }
        if ($step < 3) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '正在升级数据...',
                'progress' => 0
            );
        }
    }

    protected function updateAlipayType()
    {
        $payment = $this->getSettingService()->get('payment', array());
        if ($payment) {
            $payment['alipay_type'] = 'direct';
            $this->getSettingService()->set('payment', $payment);
        }

        return 1;
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

        return 1;
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