<?php

namespace Biz\Course\Event;

use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseSetDao;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Sync\Service\AbstractSychronizer;
use Biz\Sync\Service\SyncService;
use Biz\System\Service\LogService;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Dao\CourseMaterialDao;
use Codeages\Biz\Framework\Event\Event;
use Biz\Classroom\Service\ClassroomService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseSyncSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'classroom.update' => 'onClassroomUpdate',

            'course-set.update' => 'onCourseSetUpdate',

            'course.update' => 'onCourseUpdate',

            'course.teachers.update' => 'onCourseTeachersChange',

            'course.chapter.create' => 'onCourseChapterCreate',
            //章节的更新和删除会比较麻烦，因为还涉及子节点（比如task的引用也要切换）的处理
            'course.chapter.update' => 'onCourseChapterUpdate',
            'course.chapter.delete' => 'onCourseChapterDelete',

            'course.lesson.create' => 'onCourseChapterCreate',
            'course.lesson.update' => 'onCourseChapterUpdate',
            'course.lesson.publish' => 'onCourseChapterUpdate',
            'course.lesson.unpublish' => 'onCourseChapterUpdate',
            'course.lesson.setOptional' => 'onCourseChapterUpdate',
            //同步新建的任务时同步新增material记录即可，这里无需处理
            // 'course.material.create' => 'onCourseMaterialCreate',
            'course.material.update' => 'onCourseMaterialUpdate',
            'course.material.delete' => 'onCourseMaterialDelete',

            'course.change.showPublishLesson' => 'onCourseUpdate',
        );
    }

    public function onClassroomUpdate(Event $event)
    {
        $arguments = $event->getSubject();
        $classroom = $arguments['classroom'];

        $courses = $this->getClassroomService()->findCoursesByClassroomId($classroom['id']);
        if (empty($courses)) {
            return;
        }

        foreach ($courses as $course) {
            $this->getCourseDao()->update($course['id'], array('vipLevelId' => $classroom['vipLevelId']));
        }
    }

    public function onCourseSetUpdate(Event $event)
    {
        $courseSet = $event->getSubject();
        $this->updateCourseSetTitleByCourseSet($courseSet);
        if ($courseSet['parentId'] > 0) {
            return;
        }
        $copiedCourseSets = $this->getCourseSetDao()->findCourseSetsByParentIdAndLocked($courseSet['id'], 1);
        if (empty($copiedCourseSets)) {
            return;
        }
        foreach ($copiedCourseSets as $cc) {
            $cc = $this->copyFields($courseSet, $cc, array(
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
                'materialNum',
            ));
            $copyCourseSet = $this->getCourseSetDao()->update($cc['id'], $cc);
            $this->updateCourseSetTitleByCourseSet($copyCourseSet);
        }
    }

    protected function updateCourseSetTitleByCourseSet($courseSet)
    {
        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);
        foreach ($courses as $course) {
            $course['courseSetTitle'] = $courseSet['title'];
            $this->getCourseDao()->update($course['id'], $course);
        }
    }

    public function onCourseUpdate(Event $event)
    {
        $course = $event->getSubject();
        if ($course['parentId'] > 0) {
            return;
        }
        $this->updateCopiedCourses($course);
    }

    protected function updateCopiedCourses($course)
    {
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($course['id'], 1);
        if (empty($copiedCourses)) {
            return;
        }

        $syncFields = ArrayToolkit::parts($course, array(
            'title',
            'courseSetTitle',
            'learnMode',
            'summary',
            'goals',
            'audiences',
            'isFree',
            'price',
            // 'vipLevelId',
            'buyable',
            'tryLookable',
            'tryLookLength',
            'watchLimit',
            'services',
            'taskNum',
            'compulsoryTaskNum',
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
            'rewardPoint',
            'taskRewardPoint',
            'maxStudentNum',
            'isHideUnpublish',
            'lessonNum',
            'publishLessonNum',
            'enableAudio',
        ));
        $this->getCourseDao()->update(array('parentId' => $course['id'], 'locked' => 1), $syncFields);
    }

    public function onCourseTeachersChange(Event $event)
    {
        $course = $event->getSubject();
        $teachers = $event->getArgument('teachers');
        if ($course['parentId'] > 0) {
            return;
        }

        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($course['id'], 1);

        if (empty($copiedCourses)) {
            return;
        }

        foreach ($copiedCourses as $cc) {
            $classroom = $this->getClassroomService()->getClassroomByCourseId($cc['id']);

            if (empty($classroom)) {
                continue;
            }

            $this->setCourseTeachers($cc, $teachers);
            $this->getClassroomService()->updateClassroomTeachers($classroom['id']);
        }
    }

    public function onCourseChapterCreate(Event $event)
    {
        $chapter = $event->getSubject();
        if ($chapter['copyId'] > 0) {
            return;
        }

        $this->getSyncService()->sync('Course:CourseChapter.'.AbstractSychronizer::SYNC_WHEN_CREATE, $chapter['id']);
    }

    public function onCourseChapterUpdate(Event $event)
    {
        $chapter = $event->getSubject();
        if ($chapter['copyId'] > 0) {
            return;
        }

        $this->getSyncService()->sync('Course:CourseChapter.'.AbstractSychronizer::SYNC_WHEN_UPDATE, $chapter['id']);
    }

    public function onCourseChapterDelete(Event $event)
    {
        $chapter = $event->getSubject();
        if ($chapter['copyId'] > 0) {
            return;
        }

        $this->getSyncService()->sync('Course:CourseChapter.'.AbstractSychronizer::SYNC_WHEN_DELETE, $chapter['id']);
    }

    public function onCourseMaterialUpdate(Event $event)
    {
        $material = $event->getSubject();
        if ($material['copyId'] > 0) {
            return;
        }

        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($material['courseId'], 1);
        if (empty($copiedCourses)) {
            return;
        }
        $lockedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
        $copiedMaterials = $this->getMaterialDao()->findByCopyIdAndLockedCourseIds($material['id'], $lockedCourseIds);
        foreach ($copiedMaterials as $cm) {
            $cm = $this->copyFields($material, $cm, array(
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
        if ($material['copyId'] > 0) {
            return;
        }
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($material['courseId'], 1);
        if (empty($copiedCourses)) {
            return;
        }
        $lockedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
        $copiedMaterials = $this->getMaterialDao()->findByCopyIdAndLockedCourseIds($material['id'], $lockedCourseIds);

        foreach ($copiedMaterials as $cm) {
            $this->getMaterialDao()->delete($cm['id']);
        }
    }

    protected function setCourseTeachers($course, $teachers)
    {
        $teacherMembers = array();
        foreach (array_values($teachers) as $index => $teacher) {
            $teacherMembers[] = array(
                'courseId' => $course['id'],
                'courseSetId' => $course['courseSetId'],
                'userId' => $teacher['id'],
                'role' => 'teacher',
                'seq' => $index,
                'isVisible' => empty($teacher['isVisible']) ? 0 : 1,
            );
        }

        $existTeachers = $this->getMemberDao()->findByCourseIdAndRole($course['id'], 'teacher');

        foreach ($existTeachers as $member) {
            $this->getMemberDao()->delete($member['id']);
        }

        $visibleTeacherIds = array();

        foreach ($teacherMembers as $member) {
            $existMember = $this->getMemberDao()->getByCourseIdAndUserId($course['id'], $member['userId']);

            if ($existMember) {
                $this->getMemberDao()->delete($existMember['id']);
            }

            $member = $this->getMemberDao()->create($member);

            if ($member['isVisible']) {
                $visibleTeacherIds[] = $member['userId'];
            }
        }

        $fields = array('teacherIds' => $visibleTeacherIds);

        return $this->getCourseDao()->update($course['id'], $fields);
    }

    protected function copyFields($source, $target, $fields)
    {
        if (empty($fields)) {
            return $target;
        }
        foreach ($fields as $field) {
            if (isset($source[$field])) {
                $target[$field] = $source[$field];
            }
        }

        return $target;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
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
     * @return CourseMemberDao
     */
    protected function getMemberDao()
    {
        return $this->getBiz()->dao('Course:CourseMemberDao');
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
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }

    /**
     * @return SyncService
     */
    protected function getSyncService()
    {
        return $this->getBiz()->service('Sync:SyncService');
    }
}
