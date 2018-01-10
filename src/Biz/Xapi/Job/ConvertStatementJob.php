<?php

namespace Biz\Xapi\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class ConvertStatementJob extends AbstractJob
{
    public function execute()
    {
        try {
            $condition = array(
                'status' => 'created',
            );
            $statements = $this->getXapiService()->searchStatements($condition, array('created_time' => 'DESC'), 0, 500);
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
}