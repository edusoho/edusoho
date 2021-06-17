<?php

namespace Biz\WrongBook\Service;

interface WrongQuestionService
{
    public function buildWrongQuestion($fields);

    public function createWrongQuestion($fields);

    public function searchWrongQuestion($conditions, $orderBys, $start, $limit);

    public function deleteWrongQuestion($id);

    /**
     * BookPool
     */
    public function searchWrongBookPool($conditions, $orderBys, $start, $limit);

    public function getWrongBookPoolByFieldsGroupByTargetType($fields);
}
