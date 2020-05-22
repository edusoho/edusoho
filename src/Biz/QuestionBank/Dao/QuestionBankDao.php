<?php

namespace Biz\QuestionBank\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface QuestionBankDao extends GeneralDaoInterface
{
    /**
     * @param $courseSetId
     *
     * @return mixed
     */
    public function getByCourseSetId($courseSetId);

    public function getByItemBankId($itemBankId);

    public function findByIds($ids);

    public function findAll();
}
