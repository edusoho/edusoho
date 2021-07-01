<?php

namespace Biz\WrongBook\Service;

interface WrongQuestionService
{
    public function buildWrongQuestion($fields, $source);

    public function createWrongQuestion($fields);

    public function searchWrongQuestion($conditions, $orderBys, $start, $limit, $columns = []);

    public function deleteWrongQuestion($id);

    public function batchBuildWrongQuestion($wrongAnswerQuestionReports, $source);

    public function countWrongQuestion($conditions);

    public function getPool($poolId);

    public function getPoolBySceneId($sceneId);

    public function getWrongBookQuestionByFields($fields);

    public function searchWrongBookQuestionsByConditions($conditions, $orderBys, $start, $limit);

    public function countWrongBookQuestionsByConditions($conditions);

    /**
     * BookPool
     */
    public function searchWrongBookPool($conditions, $orderBys, $start, $limit);

    public function countWrongBookPool($conditions);

    public function getWrongBookPoolByFieldsGroupByTargetType($fields);

    public function getWrongBookPoolByFields($fields);

    public function searchWrongQuestionsWithCollect($conditions, $orderBys, $start, $limit, $columns = []);

    public function countWrongQuestionWithCollect($conditions);

    public function searchWrongQuestionsWithDistinctItem($conditions, $orderBys, $start, $limit, $columns = []);

    public function countWrongQuestionsWithDistinctItem($conditions);

    public function findWrongQuestionBySceneIds($sceneIds);

    /**
     * collect
     */
    public function searchCollect($conditions, $orderBys, $start, $limit, $columns = []);
}
