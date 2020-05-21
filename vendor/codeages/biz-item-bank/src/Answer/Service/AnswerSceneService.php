<?php

namespace Codeages\Biz\ItemBank\Answer\Service;

interface AnswerSceneService
{
    /**
     * 创建场次
     *
     * @param array $scene
     * @return AnswerScene
     */
    public function create($answerScene = array());

    /**
     *
     * 更新场次
     * @param int $id
     * @param array $scene
     * @return AnswerScene
     */
    public function update($id, $answerScene = array());

    /**
     * 获取场次
     *
     * @param int $id
     * @return AnswerScene
     */
    public function get($id);

    /**
     * 场次是否开始
     *
     * @param int $id
     * @return boolean
     */
    public function canStart($id);

    /**
     * 能否再次答题
     *
     * @param int $id
     * @param int $userId
     * @return boolean
     */
    public function canRestart($id, $userId);

    /**
     * 统计数量
     *
     * @param array $conditions
     * @return AnswerScene
     */
    public function count($conditions);

    /**
     * 搜索场次
     *
     * @param array $conditions
     * @param array $orderBys
     * @param int $start
     * @param int $limit
     * @param array $columns
     * @return AnswerScene
     */
    public function search($conditions, $orderBys, $start, $limit, $columns = array());

    /**
     * 生成场次报告
     *
     * @param int $id 场次id
     * @return void
     */
    public function buildAnswerSceneReport($id);

    /**
     * 获取场次报告
     *
     * @param int $id 场次id
     * @return AnswerSceneReport
     */
    public function getAnswerSceneReport($id);
}
