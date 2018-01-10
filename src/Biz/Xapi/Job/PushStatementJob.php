<?php

namespace Biz\Xapi\Job;

use Biz\System\Service\SettingService;
use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use AppBundle\Common\ArrayToolkit;
use QiQiuYun\SDK\Auth;

class PushStatementJob extends AbstractJob
{
    public function execute()
    {
        $xapiSetting = $this->getSettingService()->get('xapi', array());
        if (empty($xapiSetting['enabled'])) {
            return;
        }

        for ($i = 0; $i <= 5; $i++ ) {
            $this->pushStatements(500);
        }

    }

    protected function pushStatements($count)
    {
        try {
            $condition = array(
                'status' => 'converted',
            );
            $statements = $this->getXapiService()->searchStatements($condition, array('created_time' => 'DESC'), 0, $count);
            $statementIds = ArrayToolkit::column($statements, 'id');
            $uuids = ArrayToolkit::column($statements, 'uuid');
            $statements = ArrayToolkit::index($statements, 'uuid');

            $pushStatements = ArrayToolkit::column($statements, 'data');

            if (empty($pushStatements)) {
                return;
            }
            $this->getXapiService()->updateStatementsPushingByStatementIds($statementIds);
            $results = $this->createXAPIService()->pushStatements($pushStatements);

//            $pushData = array();
            $callbackIds = array();
            if (is_array($results)) {
                foreach ($results as $uuid) {
                    if (in_array($uuid, $uuids)) {
                        $callbackIds[] = $uuid;
                    }
                }
                $this->getXapiService()->updateStatusPushedAndPushedTimeByUuids($callbackIds, time());
            }
        } catch (\Exception $e) {
            $this->biz['logger']->error($e);
        }
    }

    public function createXAPIService()
    {
        $settings = $this->getSettingService()->get('storage', array());
        $siteSettings = $this->getSettingService()->get('site', array());
        $xapiSetting = $this->getSettingService()->get('xapi', array());

        $pushUrl = !empty($xapiSetting['push_url']) ? $xapiSetting['push_url'] : 'http://xapi.qiqiuyu.net/vi/';

        $siteName = empty($siteSettings['name']) ? '' : $siteSettings['name'];
        $siteUrl = empty($siteSettings['url']) ? '' : $siteSettings['url'];
        $accessKey = empty($settings['cloud_access_key']) ? '' : $settings['cloud_access_key'];
        $secretKey = empty($settings['cloud_secret_key']) ? '' : $settings['cloud_secret_key'];
        $auth = new Auth($accessKey, $secretKey);

        return new \QiQiuYun\SDK\Service\XAPIService($auth, array(
            'base_uri' => $pushUrl,
            'school' => array(
                'accessKey' => $accessKey,
                'url' => $siteUrl,
                'name' => $siteName,
            ),
        ));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->biz->service('Xapi:XapiService');
    }
}
