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
        $pool = $this->getWrongQuestionService()->getPoolBySceneId($report['answer_scene_id']);

        return $pool;
    }

    public function prepareSceneIds($poolId, $conditions)
    {
        return $conditions;
    }

    public function buildConditions($pool, $conditions)
    {
        // TODO: Implement buildConditions() method.
    }

    /**
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return $this->biz->service('WrongBook:WrongQuestionService');
    }
}
