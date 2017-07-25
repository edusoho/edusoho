<?php

namespace Biz\Course\Copy\CourseSet;

use Biz\AbstractCopy;
use Biz\Course\Dao\CourseSetDao;
use AppBundle\Common\ArrayToolkit;

class CourseSetCopy extends AbstractCopy
{
    public function preCopy($source, $options)
    {
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
            'teacherIds',
            'materialNum',
        );
    }

    public function doCopy($courseSet, $options)
    {

        //tudo 班级复制课程的 teacherIds，materialNum有误，需要在复制结束前update
        $currentNode = $this->getCurrentNode();
        $currentUser = $this->biz['user'];

        $newCourseSet = $this->filterFields($courseSet);
        $newCourseSet['status'] = 'draft';
        $newCourseSet['title'] = $options['params']['title'];
        if (!empty($currentNode['isCopy'])) {
            $newCourseSet['locked'] = 1; // 默认锁定
            $newCourseSet['parentId'] = $courseSet['id'];
            $newCourseSet['status'] = 'published';
        }
        $newCourseSet['creator'] = $currentUser->getId();

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
