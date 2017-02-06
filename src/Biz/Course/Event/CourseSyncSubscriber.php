<?php

namespace Biz\Course\Event;

use Biz\Activity\Dao\ActivityDao;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Dao\CourseMaterialDao;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\ArrayToolkit;

abstract class CourseSyncSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /*
     * @todo
     * 1. 当业务对象变更（create/update/delete）时，查找引用此对象的业务对象，所以其相关course处于locked状态，则同步
     * 2. 采用dao操作，而非service，这样可以减少副作用
     * 3. 涉及表：
     *      course_set
     *      course
     *      course_chapter
     *      course_task
     *      activity(activit_config)
     *      testpaper、testpaper_item、testpaper_activity
     *      question、question_category、question_marker
     *      course_material、upload_files（？）、upload_files_share（？）
     */

    public static function getSubscribedEvents()
    {
        return array(
            'courseSet.update'       => 'onCourseSetUpdate',

            'course.update'          => 'onCourseUpdate',

            'course.chapter.create'  => 'onCourseChapterCreate',
            //章节的更新和删除会比较麻烦，因为还涉及子节点（比如task的引用也要切换）的处理
            'course.chapter.update'  => 'onCourseChapterUpdate',
            'course.chapter.delete'  => 'onCourseChapterDelete',

            'course.material.create' => 'onCourseMaterialCreate',
            'course.material.update' => 'onCourseMaterialUpdate',
            'course.material.delete' => 'onCourseMaterialDelete'
        );
    }

    public function onCourseSetUpdate(Event $event)
    {
        $courseSet = $event->getSubject();
        if($courseSet['parentId'] > 0) {
            return;
        }
        $copiedCourseSet = $this->getCourseSetDao()->findCourseSetsByParentIdAndLocked($courseSet['id'], 1);
        if(empty($copiedCourseSet)){
            return;
        }
        foreach ($copiedCourseSet as $cc){
            $cc = $this->copyFields($cc, $courseSet, array(
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
                'orgId',
                'orgCode',
                'discountId',
                'discount',
                'maxRate',
                'materialNum'
            ));
            $this->getCourseSetDao()->update($cc['id'], $cc);
        }
    }

    public function onCourseUpdate(Event $event)
    {
        $course = $event->getSubject();
        if($course['parentId'] > 0){
            return;
        }
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($course['id'], 1);
        if(empty($copiedCourses)){
            return;
        }
        foreach ($copiedCourses as $cc)
        {
            $cc = $this->copyFields($cc, $course, array(
                'title',
                'learnMode',
                'expiryMode',
                'expiryDays',
                'expiryStartDate',
                'expiryEndDate',
                'summary',
                'goals',
                'audiences',
                'isFree',
                'price',
                'vipLevelId',
                'buyable',
                'tryLookable',
                'tryLookLength',
                'watchLimit',
                'services',
                'taskNum',
                'publishedTaskNum',
                'buyExpiryTime',
                'type',
                'approval',
                'originPrice',
                'coinPrice',
                'originCoinPrice',
                'showStudentNumType',
                'serializeMode',
                'giveCredit',
                'about',
                'locationId',
                'address',
                'deadlineNotify',
                'daysOfNotifyBeforeDeadline',
                'singleBuy',
                'freeStartTime',
                'freeEndTime',
                'cover',
                'enableFinish',
                'maxRate',
                'materialNum',
            ));
            $this->getCourseDao()->update($cc['id'], $cc);
        }
    }

    public function onCourseChapterCreate(Event $event)
    {
        $chapter = $event->getSubject();
        if($chapter['copyId'] > 0){
            return;
        }
        $parentChapter = $this->getChapterDao()->get($chapter['parentId']);
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($chapter['courseId'], 1);
        if(empty($copiedCourses)){
            return;
        }
        foreach ($copiedCourses as $cc){
            $newChapter = array(
                'type' => $chapter['type'],
                'number' => $chapter['number'],
                'seq' => $chapter['seq'],
                'title' => $chapter['title'],
                'copyId' => $chapter['id'],
                'parentId' => 0,
                'courseId' => $cc['id']
            );

            if(!empty($parentChapter)){
                $copiedParentChapters = $this->getChapterDao()->findChaptersByCopyIdAndLockedCourseIds($parentChapter['id'], array($cc['id']));
                $newChapter['parentId'] = $copiedParentChapters[0]['id'];
            }
            $this->getChapterDao()->create($newChapter);
        }
    }

    public function onCourseChapterUpdate(Event $event)
    {
        $chapter = $event->getSubject();
        if($chapter['copyId'] > 0){
            return;
        }
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($chapter['courseId'], 1);
        if(empty($copiedCourses)){
            return;
        }
        $lockedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
        $copiedChapters = $this->getChapterDao()->findChaptersByCopyIdAndLockedCourseIds($chapter['id'], $lockedCourseIds);
        foreach ($copiedChapters as $cc){
            $cc = $this->copyFields($cc, $chapter, array(
                'number',
                'seq',
                'title'
            ));
            $this->getChapterDao()->update($cc['id'], $cc);
        }
    }

    public function onCourseChapterDelete(Event $event)
    {
        $chapter = $event->getSubject();
        if($chapter['copyId'] > 0){
            return;
        }
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($chapter['courseId'], 1);
        if(empty($copiedCourses)){
            return;
        }
        $lockedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
        $copiedChapters = $this->getChapterDao()->findChaptersByCopyIdAndLockedCourseIds($chapter['id'], $lockedCourseIds);

        foreach ($copiedChapters as $cc){
            $this->getChapterDao()->delete($cc['id']);
        }
    }

    public function onCourseMaterialCreate(Event $event)
    {
        $material = $event->getSubject();
        if($material['copyId'] > 0){
            return;
        }
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($material['courseId'], 1);
        if(empty($copiedCourses)){
            return;
        }

        $copiedActivity = array();
        if(!empty($material)){
            //xxx 注意opencourse没有对应的Activity？
            $copiedActivities = $this->getActivityDao()->findByCopyId($material['lessonId']);
            $copiedActivity = $copiedActivities[0];
        }

        foreach ($copiedCourses as $cc)
        {
            $newMaterial = $this->copyFields(array(), $material, array(
                'title',
                'description',
                'link',
                'fileId',
                'fileUri',
                'fileMime',
                'fileSize',
                'source',
                'userId',
                'type',
            ));
            $newMaterial['copyId'] = $material['id'];
            $newMaterial['courseSetId'] = $cc['courseSetId'];
            $newMaterial['courseId'] = $cc['id'];
            if(!empty($copiedActivity)){
                $newMaterial['lessonId'] = $copiedActivity['id'];
            }

            $this->getMaterialDao()->create($newMaterial);
        }
    }

    public function onCourseMaterialUpdate(Event $event)
    {
        $material = $event->getSubject();
        if($material['copyId'] > 0){
            return;
        }

        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($chapter['courseId'], 1);
        if(empty($copiedCourses)){
            return;
        }
        $lockedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
        $copiedMaterials = $this->getMaterialDao()->findByCopyIdAndLockedCourseIds($material['id'], $lockedCourseIds);
        foreach ($copiedMaterials as $cm){
            $cm = $this->copyFields($cm, $material, array(
                'title',
                'description',
                'link',
                'fileId',
                'fileUri',
                'fileMime',
                'fileSize',
                'userId',
            ));
            $this->getMaterialDao()->update($cm['id'], $cm);
        }
    }

    public function onCourseMaterialDelete(Event $event)
    {
        $material = $event->getSubject();
        if($material['copyId'] > 0){
            return;
        }
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($chapter['courseId'], 1);
        if(empty($copiedCourses)){
            return;
        }
        $lockedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
        $copiedMaterials = $this->getMaterialDao()->findByCopyIdAndLockedCourseIds($material['id'], $lockedCourseIds);

        foreach ($copiedMaterials as $cm){
            $this->getMaterialDao()->delete($cm['id']);
        }
    }

    protected function copyFields($source, $target, $fields)
    {
        if(empty($fields)){
            return $target;
        }
        foreach ($fields as $field){
            if(!empty($source[$field])){
                $target[$field] = $source[$field];
            }
        }

        return $target;
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->getBiz()->dao('Course:CourseDao');
    }

    /**
     * @return CourseSetDao
     */
    protected function getCourseSetDao()
    {
        return $this->getBiz()->dao('Course:CourseSetDao');
    }

    /**
     * @return CourseMaterialDao
     */
    protected function getMaterialDao()
    {
        return $this->getBiz()->dao('Course:CourseMaterialDao');
    }

    /**
     * @return CourseChapterDao
     */
    protected function getChapterDao()
    {
        return $this->getBiz()->dao('Course:CourseChapterDao');

    }

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        //fixme 不应该调用course模块之外的dao对象
        return $this->getBiz()->dao('Activity:ActivityDao');
    }
}
