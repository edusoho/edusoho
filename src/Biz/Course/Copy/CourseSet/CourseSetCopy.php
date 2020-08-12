<?php

namespace Biz\Course\Copy\CourseSet;

use Biz\AbstractCopy;
use Biz\Course\Dao\CourseSetDao;
use Biz\Goods\Mediator\CourseSetGoodsMediator;

class CourseSetCopy extends AbstractCopy
{
    public function preCopy($source, $options)
    {
    }

    protected function getFields()
    {
        return [
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
            'orgId',
            'orgCode',
            'teacherIds',
            'materialNum',
        ];
    }

    public function doCopy($courseSet, $options)
    {
        //tudo 班级复制课程的 teacherIds，materialNum有误，需要在复制结束前update
        $currentNode = $this->getCopyChain();
        $currentUser = $this->biz['user'];

        $newCourseSet = $this->partsFields($courseSet);
        $newCourseSet['status'] = 'draft';
        if (!empty($options['params']['title'])) {
            $newCourseSet['title'] = $options['params']['title'];
        }

        if (!empty($currentNode['isCopy'])) {
            $newCourseSet['locked'] = 1; // 默认锁定
            $newCourseSet['parentId'] = $courseSet['id'];
            $newCourseSet['status'] = 'published';
        }
        $newCourseSet['creator'] = $currentUser->getId();
        $newCourseSet = $this->getCourseSetDao()->create($newCourseSet);
        $this->getCourseSetGoodsMediator()->onCreate($newCourseSet);
        $this->getCourseSetGoodsMediator()->onUpdateNormalData($newCourseSet);
        if ('published' === $newCourseSet['status']) {
            $this->getCourseSetGoodsMediator()->onPublish($newCourseSet);
        }

        return ['newCourseSet' => $newCourseSet];
    }

    /**
     * @return CourseSetDao
     */
    private function getCourseSetDao()
    {
        return $this->biz->dao('Course:CourseSetDao');
    }

    /**
     * @return CourseSetGoodsMediator
     */
    protected function getCourseSetGoodsMediator()
    {
        return $this->biz['goods.mediator.course_set'];
    }
}
