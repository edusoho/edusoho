<?php

namespace Biz\Xapi\Job;

use Biz\System\Service\SettingService;
use Biz\Xapi\Dao\StatementDao;
use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class PushStatementJob extends AbstractJob
{
    public function execute()
    {
        $statements = $this->getXapiService()->searchStatements(
            array(
                'status' => 'pushed',
            ),
            array('push_time' => 'ASC'),
            0,
            1000
        );

        $this->getStatementDao()->batchCreate($statements);
        foreach ($statements as $statement) {
            $this->getStatementDao()->delete($statement['id']);
        }
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

    /**
     * @return StatementDao
     */
    protected function getStatementDao()
    {
        return $this->biz->dao('Xapi:XapiDao');
    }
}
