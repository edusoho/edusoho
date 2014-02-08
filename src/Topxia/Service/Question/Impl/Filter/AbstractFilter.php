<?php
namespace Topxia\Service\Question\Impl\Filter;

use Topxia\Service\Common\BaseService;

abstract class AbstractFilter extends BaseService
{

    protected function commonFilter($fields, $mode)
    {
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
        $filtered['parentId'] = empty($fields['parentId']) ? 0 : (int)$fields['parentId'];
        if ($mode == 'update') {
            unset($filtered['parentId']);
        }

        $filtered['updatedTime'] = time();
        if ($mode == 'create') {
            $filtered['createdTime'] = time();
        }

        $filtered['target'] = empty($fields['target']) ? '' : $fields['target'];

        return $filtered;
    }

    abstract public function filter($fields, $mode = 'create');
}