<?php

namespace Biz\ItemBankExercise\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\ItemBankExercise\Dao\ItemBankExerciseDao;
use Biz\ItemBankExercise\Service\ItemBankExerciseService;
use Biz\QuestionBank\Dao\QuestionBankDao;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\MemberService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Taxonomy\CategoryException;
use Codeages\Biz\ItemBank\ItemBank\Service\ItemBankService;

class ItemBankExerciseServiceImpl extends BaseService implements ItemBankExerciseService
{
    public function countCourses($conditions)
    {
        return $this->getItemBankExerciseDao()->count($conditions);
    }

    public function searchCourses($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getItemBankExerciseDao()->search($conditions, $orderBy, $start, $limit);
    }

    protected function _prepareCourseConditions($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if (0 == $value) {
                return true;
            }

            return !empty($value);
        });

        return $conditions;
    }

    /**
     * @return ItemBankExerciseDao
     */
    protected function getItemBankExerciseDao()
    {
        return $this->createDao('ItemBankExercise:ItemBankExerciseDao');
    }
}
