<?php

namespace Biz\Sync\Service;

use Biz\Common\CommonException;
use Codeages\Biz\Framework\Context\BizAware;
use Codeages\Biz\Framework\Dao\BatchCreateHelper;
use Codeages\Biz\Framework\Dao\BatchHelperInterface;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

abstract class AbstractSychronizer extends BizAware
{
    const BATCH_CREATE_HELPER = 'c';
    const BATCH_UPDATE_HELPER = 'u';
    const BATCH_DELETE_HELPER = 'd';

    const SYNC_WHEN_CREATE = 'syncWhenCreate';
    const SYNC_WHEN_UPDATE = 'syncWhenUpdate';
    const SYNC_WHEN_DELETE = 'syncWhenDelete';

    protected $batchHelperList;

    public function __construct()
    {
        $this->batchHelperList = array();
    }

    abstract public function syncWhenCreate($sourceId);

    abstract public function syncWhenUpdate($sourceId);

    abstract public function syncWhenDelete($sourceId);

    public function flush()
    {
        foreach ($this->batchHelperList as $batchHelper) {
            $batchHelper->flush();
        }
    }

    /**
     * @param $batchType
     * @param $dao
     *
     * @return BatchHelperInterface
     */
    protected function getBatchHelper($batchType, $dao)
    {
        if (empty($this->batchHelperList[$batchType])) {
            switch ($batchType) {
                case self::BATCH_CREATE_HELPER:
                    $this->batchHelperList[$batchType] = new BatchCreateHelper($dao);
                    break;
                case self::BATCH_UPDATE_HELPER:
                    $this->batchHelperList[$batchType] = new BatchUpdateHelper($dao);
                    break;
                default:
                    throw CommonException::ERROR_PARAMETER();
            }
        }

        return $this->batchHelperList[$batchType];
    }
}
