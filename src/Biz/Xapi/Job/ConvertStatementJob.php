<?php

namespace Biz\Xapi\Job;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\TimeMachine;
use Biz\Xapi\Dao\StatementDao;
use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class ConvertStatementJob extends AbstractJob
{
    private $perCount = 2000;

    public function execute()
    {
        try {
            $this->getStatementDao()->retryStatusPushingToCreatedByCreatedTime(strtotime('-3 day', TimeMachine::time()));
            $condition = array(
                'status' => 'created',
            );
            $statements = $this->getXapiService()->searchStatements($condition, array('created_time' => 'DESC'), 0, $this->perCount);
            $statements = ArrayToolkit::index($statements, 'uuid');

            foreach ($statements as &$statement) {
                if (!empty($statement['verb']) && !empty($statement['target_type'])) {
                    $statement['key'] = "{$statement['verb']}_{$statement['target_type']}";
                }
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

            $pushData = array();
            $pushStatements = ArrayToolkit::index($pushStatements, 'id');
            foreach ($pushStatements as $key => $data) {
                if (isset($statements[$key])) {
                    $pushData[$statements[$key]['id']] = $data;
                }
            }

            $this->getXapiService()->updateStatementsConvertedAndDataByStatementData($pushData);
        } catch (\Exception $e) {
            $this->biz['logger']->error($e);
        }
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->biz->service('Xapi:XapiService');
    }

    /**
     * @return StatementDao
     */
    protected function getStatementDao()
    {
        return $this->biz->dao('Xapi:StatementDao');
    }
}
