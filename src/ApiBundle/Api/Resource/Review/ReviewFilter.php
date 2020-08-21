<?php

namespace ApiBundle\Api\Resource\Review;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class ReviewFilter extends Filter
{
    protected $publicFields = [
        'id', 'userId', 'user', 'targetId', 'targetType', 'target', 'targetName', 'content', 'rating', 'parentId', 'createdTime', 'updatedTime', 'posts',
    ];

    protected $targetFilters = [
        'course' => 'ApiBundle\Api\Resource\Course\CourseFilter',
        'classroom' => 'ApiBundle\Api\Resource\Classroom\ClassroomFilter',
        'item_bank_exercise' => 'ApiBundle\Api\Resource\ItemBankExercise\ItemBankExerciseFilter',
    ];

    protected function publicFields(&$data)
    {
        if (!empty($data['user'])) {
            $userFilter = new UserFilter();
            $userFilter->setMode(Filter::SIMPLE_MODE);
            $userFilter->filter($data['user']);
        }

        if (!empty($data['posts'])) {
            $postFilter = new ReviewPostFilter();
            $postFilter->setMode(Filter::SIMPLE_MODE);
            $postFilter->filters($data['posts']);
        }

        if (in_array($data['targetType'], array_keys($this->targetFilters))) {
            $class = $this->targetFilters[$data['targetType']];
            $targetFilter = new $class();
            $targetFilter->setMode(Filter::SIMPLE_MODE);
            $targetFilter->filter($data['target']);
        }
    }
}
