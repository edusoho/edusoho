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
            $statements = $this->getXapiService()->searchStatements($condition, array('created_time' => 'DESC'), 0, 500);
            $statementIds = ArrayToolkit::column($statements, 'id');
            $statements = ArrayToolkit::index($statements, 'uuid');

            foreach ($statements as &$statement) {
                $statement['key'] = "{$statement['verb']}_{$statement['target_type']}";
            }

            $groupStatements = ArrayToolkit::group($statements, 'key');
            $pushStatements = array();
            foreach ($groupStatements as $key => $values) {
                $push = $this->biz["xapi.push.{$key}"];
                $result = $push->packages($values);
                if (is_array($result)) {
                    $pushStatements = array_merge($pushStatements, $result);
                }
            }

            if (empty($pushStatements)) {
                return;
            }

            $this->getXapiService()->updateStatementsPushingByStatementIds($statementIds);
            $results = $this->createXAPIService()->pushStatements($pushStatements);

            $pushData = array();
            if (is_array($results)) {
                $pushStatements = ArrayToolkit::index($pushStatements, 'id');
                foreach ($pushStatements as $key => $data) {
                    if (!in_array($key, $results)) {
                        $this->biz['logger']->info($results);
                        unset($pushStatements[$key]);
                        continue;
                    }

                    if (isset($statements[$key])) {
                        $pushData[$statements[$key]['id']] = $data;
                    }
                }
                $this->getXapiService()->updateStatementsPushedAndDataByStatementData($pushData);
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
