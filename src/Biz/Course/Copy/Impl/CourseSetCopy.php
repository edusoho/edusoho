<?php

namespace Biz\Course\Copy\Impl;

use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Course\Dao\CourseSetDao;

class CourseSetCopy extends AbstractEntityCopy
{
    public function __construct($biz)
    {
        parent::__construct($biz, 'activity');
    }

    protected function copyEntity($source, $config = array())
    {
        return $this->doCopyCourseSet($source);
    }

    protected function getFields()
    {
        return array(
            'type',
            'title',
            'subtitle',
            'tags',
            'categoryId',
            'serializeMode',
            'summary',
            'goals',
            'audiences',
            'cover',
            'categoryId',
            'recommended',
            'recommendedSeq',
            'recommendedTime',
            'discountId',
            'discount',
            'orgId',
            'orgCode',
        );
    }

    private function doCopyCourseSet($courseSet)
    {
        $fields = $this->getFields();
        $newCourseSet = array(
            'parentId' => $courseSet['id'],
            'status' => 'published',
            'creator' => $this->biz['user']['id'],
            'locked' => 1, // 默认锁定
        );

        foreach ($fields as $field) {
            if (!empty($courseSet[$field]) || $courseSet[$field] == 0) {
                $newCourseSet[$field] = $courseSet[$field];
            }
        }

        return $this->getCourseSetDao()->create($newCourseSet);
    }

    /**
     * @return CourseSetDao
     */
    private function getCourseSetDao()
    {
        return $this->biz->dao('Course:CourseSetDao');
    }
}
