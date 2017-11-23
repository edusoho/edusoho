<?php

namespace Biz\Xapi\Job;

use Biz\System\Service\SettingService;
use Biz\Xapi\Dao\StatementArchiveDao;
use Biz\Xapi\Dao\StatementDao;
use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class ArchiveStatementJob extends AbstractJob
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

        $this->getArchiveStatementDao()->batchCreate($statements);
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

    /**
     * @return StatementArchiveDao
     */
    protected function getArchiveStatementDao()
    {
        return $this->biz->dao('Xapi:StatementArchiveDao');
    }
}
