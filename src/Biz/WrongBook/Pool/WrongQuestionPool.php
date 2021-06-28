<?php

namespace Biz\WrongBook\Pool;

use Biz\WrongBook\Service\WrongQuestionService;

/**
 * Class WrongQuestionPool 自身错题记录
 */
class WrongQuestionPool extends AbstractPool
{
    public function getPoolTarget($report)
    {
        $poolId = $this->getWrongQuestionService()->getPool($report['poolId']);
        $result = $this->getWrongQuestionService()->getPool(1);
    }

    public function prepareSceneIds($poolId, $conditions)
    {
        return $conditions;
    }

    public function prepareConditions($poolId, $conditions)
    {
        return $conditions;
    }

    /**
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return $this->biz->service('WrongBook:WrongQuestionService');
    }
}
