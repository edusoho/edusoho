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

        try {
            $condition = array(
                'status' => 'created',
            );
            $statements = $this->getXapiService()->searchStatements($condition, array('created_time' => 'ASC'), 0, 100);
            $statementIds = ArrayToolkit::column($statements, 'id');

            foreach ($statements as &$statement) {
                $statement['key'] = "{$statement['verb']}_{$statement['target_type']}";
            }

            $groupStatements = ArrayToolkit::group($statements, 'key');

            $pushStatements = array();
            foreach ($groupStatements as $key => $values) {
                $push = $this->biz["xapi.push.{$key}"];
                $pushStatements = array_merge($pushStatements, $push->packages($values));
            }

            if (empty($pushStatements)) {
                return;
            }

            $this->getXapiService()->updateStatementsPushingByStatementIds($statementIds);
            $results = $this->createXAPIService()->pushStatements($pushStatements);

            if (is_array($results)) {
                foreach ($pushStatements as $key => $data) {
                    if (!in_array($data['id'], $results)) {
                        $this->biz['logger']->info($results);
                        unset($pushStatements[$key]);
                    }
                }
                $this->getXapiService()->updateStatementsPushedAndDataByStatementData($pushStatements);
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
