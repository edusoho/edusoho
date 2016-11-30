<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Task\Strategy\StrategyContext;
use Biz\Task\Service\TaskService;
use Topxia\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;

class CourseServiceImpl extends BaseService implements CourseService
{
    public function getCourse($id)
    {
        return $this->getCourseDao()->get($id);
    }

    public function findCoursesByCourseSetId($courseSetId)
    {
        return $this->getCourseDao()->findCoursesByCourseSetId($courseSetId);
    }

    public function getDefaultCourseByCourseSetId($courseSetId)
    {
        return $this->getDefaultCourseByCourseSetId($courseSetId);
    }

    public function createCourse($course)
    {
        if (!$this->hasCourseManagerRole()) {
            throw $this->createAccessDeniedException('You have no access to Course Management');
        }
        if (!isset($course['isDefault'])) {
            $course['isDefault'] = 0;
        }
        $course = ArrayToolkit::parts($course, array(
            'title',
            'courseSetId',
            'learnMode',
            'expiryMode',
            'expiryDays',
            'expiryStartDate',
            'expiryEndDate',
            'isDefault'
        ));

        if (!ArrayToolkit::requireds($course, array('title', 'courseSetId', 'expiryMode', 'learnMode'))) {
            throw $this->createInvalidArgumentException("Lack of required fields");
        }
        if (!in_array($course['learnMode'], array('freeOrder', 'byOrder'))) {
            throw $this->createInvalidArgumentException("Param Invalid: LearnMode");
        }

        $course = $this->validateExpiryMode($course);

        $course['status'] = 'draft';

        return $this->getCourseDao()->create($course);
    }

    public function updateCourse($id, $fields)
    {
        $course = $this->tryManageCourse($id);
        $fields = ArrayToolkit::parts($fields, array(
            'title',
            'courseSetId',
            // 'learnMode', //一旦创建，学习模式不允许变更
            'expiryMode',
            'expiryDays',
            'expiryStartDate',
            'expiryEndDate',
            'summary',
            'goals',
            'audiences'
        ));

        if ($course['status'] == 'published') {
            unset($fields['expiryMode']);
            unset($fields['expiryDays']);
            unset($fields['expiryStartDate']);
            unset($fields['expiryEndDate']);
        }

        $existCourse = $this->getCourse($id);
        if (isset($existCourse['status']) && $existCourse['status'] === 'published') {
            if (!ArrayToolkit::requireds($course, array('title', 'courseSetId'))) {
                throw $this->createInvalidArgumentException("Lack of required fields");
            }
        } elseif (!ArrayToolkit::requireds($course, array('title', 'courseSetId', 'expiryMode'))) {
            throw $this->createInvalidArgumentException("Lack of required fields");
        } else {
            $fields = $this->validateExpiryMode($fields);
        }

        return $this->getCourseDao()->update($id, $fields);
    }

    public function deleteCourse($id)
    {
        $course = $this->tryManageCourse($id);
        if ($course['status'] == 'published') {
            throw $this->createAccessDeniedException("Deleting published Course is not allowed");
        }

        return $this->getCourseDao()->delete($id);
    }

    public function closeCourse($id)
    {
        $course = $this->tryManageCourse($id);
        if ($course['status'] != 'published') {
            throw $this->createAccessDeniedException('Course has not bean published');
        }
        $course['status'] = 'closed';

        $this->getCourseDao()->update($id, $course);
    }

    public function publishCourse($id, $userId)
    {
        $course = $this->tryManageCourse($id);
        $this->getCourseDao()->update($id, array(
            'status' => 'published'
        ));
    }

    protected function validateExpiryMode($course)
    {
        if ($course['expiryMode'] === 'days') {
            $course['expiryStartDate'] = null;
            $course['expiryEndDate']   = null;
        } elseif ($course['expiryMode'] === 'date') {
            $course['expiryDays'] = 0;
            if (isset($course['expiryStartDate'])) {
                $course['expiryStartDate'] = strtotime($course['expiryStartDate']);
            } else {
                throw $this->createInvalidArgumentException("Param Required: expiryStartDate");
            }
            if (isset($course['expiryEndDate'])) {
                $course['expiryEndDate'] = strtotime($course['expiryEndDate']);
            } else {
                throw $this->createInvalidArgumentException("Param Required: expiryEndDate");
            }
            if ($course['expiryEndDate'] <= $course['expiryStartDate']) {
                throw $this->createInvalidArgumentException("Value of Params expiryEndDate must later than expiryStartDate");
            }
        } else {
            throw $this->createInvalidArgumentException("Param Invalid: expiryMode");
        }

        return $course;
    }

    public function findCourseItems($courseId)
    {
        $course = $this->getCourse($courseId);
        return $this->createCourseStrategy($course)->findCourseItems($courseId);
    }

    public function tryManageCourse($courseId, $courseSetId = 0)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException("Unauthorized");
        }

        $course = $this->getCourseDao()->get($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException("Course#{$courseId} Not Found");
        }
        if ($courseSetId > 0 && $course['courseSetId'] !== $courseSetId) {
            throw $this->createInvalidArgumentException('Invalid Argument: Course#{$courseId} not in CoruseSet#{$courseSetId}');
        }
        if (!$this->hasCourseManagerRole($courseId)) {
            throw $this->createAccessDeniedException("Unauthorized");
        }

        return $course;
    }

    public function isCourseStudent($courseId, $userId)
    {
        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if (!$member) {
            return false;
        } else {
            return empty($member) || $member['role'] != 'student' ? false : true;
        }
    }

    public function tryTakeCourse($courseId)
    {
        $course = $this->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException("Course#{$courseId} Not Found");
        }
        if (!$this->canTakeCourse($course)) {
            throw $this->createAccessDeniedException("You have no access to the course#{$courseId} before you buy it");
        }
        $user   = $this->getCurrentUser();
        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($course['id'], $user['id']);
        return array($course, $member);
    }

    protected function canTakeCourse($course)
    {
        $course = !is_array($course) ? $this->getCourse(intval($course)) : $course;


        if (empty($course)) {
            return false;
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }

        if ($user->hasPermission('admin_course')) {
            return true;
        }


        if ($course['parentId'] && $this->isClassroomMember($course, $user['id'])) {
            return true;
        }
        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($course['id'], $user['id']);


        if ($member && in_array($member['role'], array('teacher', 'student'))) {
            return true;
        }

        return false;
    }

    //TODO 任务需要在排序时处理 chapterId， number
    public function sortCourseItems($courseId, $ids)
    {
        $this->tryManageCourse($courseId);

        $parentChapters = array(
            'lesson'  => array(),
            'unit'    => array(),
            'chapter' => array()
        );

        $chapterTypes = array('chapter' => 3, 'unit' => 2, 'lesson' => 1);

        foreach ($ids as $key => $id) {
            if (strpos($id, 'chapter') === 0) {
                $id      = str_replace('chapter-', '', $id);
                $chapter = $this->getChapterDao()->get($id);
                $fileds  = array('seq' => $key);

                $index = $chapterTypes[$chapter['type']];
                switch ($index) {
                    case 3:
                        $fileds['parentId'] = 0;
                        break;
                    case 2:
                        if (!empty($parentChapters['chapter'])) {
                            $fileds['parentId'] = $parentChapters['chapter']['id'];
                        }
                        break;
                    case 1:
                        if (!empty($parentChapters['unit'])) {
                            $fileds['parentId'] = $parentChapters['unit']['id'];
                        } elseif (!empty($parentChapters['chapter'])) {
                            $fileds['parentId'] = $parentChapters['chapter']['id'];
                        }
                        break;
                    default:
                        break;
                }

                if (!empty($parentChapters[$chapter['type']])) {
                    $fileds['number'] = $parentChapters[$chapter['type']]['number'] + 1;
                } else {
                    $fileds['number'] = 1;
                }

                foreach ($chapterTypes as $type => $value) {
                    if ($value < $index) {
                        $parentChapters[$type] = array();
                    }
                }

                $chapter                          = $this->getChapterDao()->update($id, $fileds);
                $parentChapters[$chapter['type']] = $chapter;
            }

            if (strpos($id, 'task') === 0) {
                $id = str_replace('task-', '', $id);

                foreach ($parentChapters as $parent) {
                    if (!empty($parent)) {
                        $this->getTaskService()->updateSeq($id, array(
                            'seq'             => $key,
                            'courseChapterId' => $parent['id']
                        ));
                        break;
                    }
                }
            }
        }
    }

    public function createChapter($chapter)
    {
        if (!in_array($chapter['type'], array('chapter', 'unit', 'lesson'))) {
            throw $this->createInvalidArgumentException("Invalid Chapter Type");
        }

        if (in_array($chapter['type'], array('unit', 'lesson'))) {
            list($chapter['number'], $chapter['parentId']) = $this->getNextNumberAndParentId($chapter['courseId']);
        } else {
            $chapter['number']   = $this->getNextChapterNumber($chapter['courseId']);
            $chapter['parentId'] = 0;
        }

        $chapter['seq']         = $this->getNextCourseItemSeq($chapter['courseId']);
        $chapter['createdTime'] = time();
        $chapter                = $this->getChapterDao()->create($chapter);
        return $chapter;
    }

    public function getNextNumberAndParentId($courseId)
    {
        $lastChapter = $this->getChapterDao()->getLastChapterByCourseIdAndType($courseId, 'chapter');

        $parentId = empty($lastChapter) ? 0 : $lastChapter['id'];

        $num = 1 + $this->getChapterDao()->getChapterCountByCourseIdAndTypeAndParentId($courseId, 'unit', $parentId);

        return array($num, $parentId);
    }

    protected function getNextChapterNumber($courseId)
    {
        //有逻辑缺陷
        $counter = $this->getChapterDao()->getChapterCountByCourseIdAndType($courseId, 'chapter');
        return $counter + 1;
    }

    public function getNextCourseItemSeq($courseId)
    {
        $chapterMaxSeq = $this->getChapterDao()->getChapterMaxSeqByCourseId($courseId);
        $taskMaxSeq    = $this->getTaskService()->getMaxSeqByCourseId($courseId);
        return ($chapterMaxSeq > $taskMaxSeq ? $chapterMaxSeq : $taskMaxSeq) + 1;
    }

    public function updateChapter($courseId, $chapterId, $fields)
    {
        $argument = $fields;
        $course   = $this->tryManageCourse($courseId);
        $chapter  = $this->getChapterDao()->get($chapterId);

        if (empty($chapter) || $chapter['courseId'] != $courseId) {
            throw $this->createNotFoundException("Chapter#{$chapterId} Not Found");
        }

        $fields  = ArrayToolkit::parts($fields, array('title', 'number', 'seq', 'parentId'));
        $chapter = $this->getChapterDao()->update($chapterId, $fields);

        return $chapter;
    }

    public function deleteChapter($courseId, $chapterId)
    {
        $course = $this->tryManageCourse($courseId);

        $deletedChapter = $this->getChapterDao()->get($chapterId);

        if (empty($deletedChapter) || $deletedChapter['courseId'] != $courseId) {
            throw $this->createNotFoundException("Chapter#{$chapterId} Not Found");
        }

        $this->getChapterDao()->delete($deletedChapter['id']);

        $prevChapter = array('id' => 0);

        foreach ($this->getChapterDao()->findChaptersByCourseId($course['id']) as $chapter) {
            if ($chapter['number'] < $deletedChapter['number']) {
                $prevChapter = $chapter;
            }
        }

        $tasks = $this->getTaskService()->findTasksByChapterId($deletedChapter['id']);

        foreach ($tasks as $task) {
            $this->getTaskService()->updateTask($task['id'], array('courseChapterId' => $prevChapter['id']));
        }
    }

    public function getChapter($courseId, $chapterId)
    {
        $chapter = $this->getChapterDao()->get($chapterId);
        $course  = $this->getCourseDao()->get($courseId);
        if ($course['id'] == $chapter['courseId']) {
            return $chapter;
        }
        return array();
    }

    protected function hasCourseManagerRole($courseId = 0)
    {
        $userId = $this->getCurrentUser()->getId();
        //TODO
        //1. courseId为空，判断是否有创建教学计划的权限
        //2. courseId不为空，判断是否有该教学计划的管理权限
        return true;
    }

    protected function createCourseStrategy($course)
    {
        return StrategyContext::getInstance()->createStrategy($course['isDefault'], $this->biz);
    }

    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    protected function getMemberDao()
    {
        return $this->createDao('Course:CourseMemberDao');
    }

    protected function getChapterDao()
    {
        return $this->createDao('Course:CourseChapterDao');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }
}
