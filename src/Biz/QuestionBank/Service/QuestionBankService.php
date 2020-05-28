<?php

namespace Biz\QuestionBank\Service;

use Biz\System\Annotation\Log;

interface QuestionBankService
{
    /**
     * @param $id
     *
     * @return mixed
     */
    public function getQuestionBank($id);

    public function getQuestionBankByCourseSetId($courseSetId);

    public function getQuestionBankByItemBankId($itemBankId);

    public function findQuestionBanksByIds($ids);

    public function searchQuestionBanks($conditions, $orderBys, $start, $limit, $columns = []);

    public function countQuestionBanks($conditions);

    /**
     * @param $fields
     *
     * @return mixed
     * @Log(module="question_bank",action="create")
     */
    public function createQuestionBank($fields);

    public function updateQuestionBankWithMembers($id, $fields, $members);

    public function updateQuestionBank($id, $fields);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="question_bank",action="delete",funcName="getQuestionBank")
     */
    public function deleteQuestionBank($id);

    public function canManageBank($bankId);

    public function findUserManageBanks();
}
