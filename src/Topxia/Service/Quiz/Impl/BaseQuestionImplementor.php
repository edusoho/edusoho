<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Quiz\QuestionImplementor;

abstract class BaseQuestionImplementor extends BaseService implements QuestionImplementor
{
    protected function filterQuestionFields($fields)
    {
        if (!in_array($fields['type'], array('choice','single_choice', 'uncertain_choice', 'fill', 'material', 'essay', 'determine'))) {
                throw $this->createServiceException('question type error！');
        }

        $filtered = array();
        $filtered['type'] = $fields['type'];
        $filtered['stem'] = empty($fields['stem']) ? '' : $this->purifyHtml($fields['stem']);
        $filtered['difficulty'] = empty($fields['difficulty']) ? 'simple': $fields['difficulty'];
        $filtered['userId'] = $this->getCurrentUser()->id;
        $filtered['answer'] = empty($fields['answer']) ? array() : $fields['answer'];
        $filtered['analysis'] = empty($fields['analysis']) ? '': $fields['analysis'];
        $filtered['metas'] = empty($fields['metas']) ? array() : $fields['metas'];
        $filtered['score'] = empty($fields['score'])? 0 : $fields['score'];
        $filtered['categoryId'] = empty($fields['categoryId']) ? 0 : (int) $fields['categoryId'];
        $filtered['parentId'] = empty($fields['parentId']) ? 0 : (int) trim($fields['parentId']);
        $filtered['updatedTime'] = time();
        if (!empty($fields['createdTime'])) {
            $filtered['createdTime'] = $fields['createdTime'];
        }
        if(!empty($fields['target'])){
            $target = explode('-', $fields['target']);
            if (count($target) != 2){
                throw $this->createServiceException("target参数不正确");
            }

            $filtered['targetType'] = (string) $target['0'];
            $filtered['targetId'] = (int) $target['1'];
        }

        return $filtered;
    }
}