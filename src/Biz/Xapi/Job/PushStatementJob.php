<?php

namespace Biz\Xapi\Job;

use Biz\System\Service\SettingService;
use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use AppBundle\Common\ArrayToolkit;

class PushStatementJob extends AbstractJob
{
    public function execute()
    {
        $xapiSetting = $this->getSettingService()->get('xapi', array());
        if (empty($xapiSetting['enabled'])) {
            return;
        }

        for ($i = 0; $i <= 5; ++$i) {
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

            $callbackIds = array();
            if (is_array($results)) {
                foreach ($results as $uuid) {
                    if (in_array($uuid, $uuids)) {
                        $callbackIds[] = $uuid;
                    } else {
                        $this->biz['logger']->info('XAPI PUSH ERROR:', array('message' => $uuid));
                    }
                }
                $this->getXapiService()->updateStatusPushedAndPushedTimeByUuids($callbackIds, time());
            }
        } catch (\Exception $e) {
            $this->biz['logger']->error($e);
        }
    }

    /**
     * @return \QiQiuYun\SDK\Service\XAPIService
     */
    public function createXAPIService()
    {
        return $this->getXapiService()->getXapiSdk();
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
