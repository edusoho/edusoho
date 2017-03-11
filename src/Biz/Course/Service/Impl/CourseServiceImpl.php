<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\Impl\CourseMemberDaoImpl;
use Biz\Course\Dao\Impl\FavoriteDaoImpl;
use Biz\Course\Dao\ThreadDao;
use Biz\Course\Dao\CourseSetDao;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Copy\Impl\CourseCopy;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ReviewService;
use Biz\Task\Strategy\StrategyContext;
use Biz\Course\Service\MaterialService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\CourseNoteService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseDeleteService;

class CourseServiceImpl extends BaseService implements CourseService
{
    public function getCourse($id)
    {
        return $this->getCourseDao()->get($id);
    }

    public function findCoursesByIds($ids)
    {
        $courses = $this->getCourseDao()->findCoursesByIds($ids);

        return ArrayToolkit::index($courses, 'id');
    }

    public function findCoursesByCourseSetIds(array $setIds)
    {
        return $this->getCourseDao()->findByCourseSetIds($setIds);
    }

    public function findCoursesByCourseSetId($courseSetId)
    {
        return $this->getCourseDao()->findCoursesByCourseSetIdAndStatus($courseSetId, null);
    }

    public function findCoursesByParentIdAndLocked($parentId, $locked)
    {
        return $this->getCourseDao()->findCoursesByParentIdAndLocked($parentId, $locked);
    }

    public function findPublishedCoursesByCourseSetId($courseSetId)
    {
        return $this->getCourseDao()->findCoursesByCourseSetIdAndStatus($courseSetId, 'published');
    }

    public function getDefaultCourseByCourseSetId($courseSetId)
    {
        return $this->getCourseDao()->getDefaultCourseByCourseSetId($courseSetId);
    }

    public function getDefaultCoursesByCourseSetIds($courseSetIds)
    {
        return $this->getCourseDao()->getDefaultCoursesByCourseSetIds($courseSetIds);
    }

    public function getFirstPublishedCourseByCourseSetId($courseSetId)
    {
        $courses = $this->searchCourses(
            array(
                'courseSetId' => $courseSetId,
                'status' => 'published',
            ),
            array('createdTime' => 'ASC'),
            0,
            1
        );

        return array_shift($courses);
    }

    public function getFirstCourseByCourseSetId($courseSetId)
    {
        $courses = $this->searchCourses(
            array(
                'courseSetId' => $courseSetId,
            ),
            array('createdTime' => 'ASC'),
            0,
            1
        );

        return array_shift($courses);
    }

    public function createCourse($course)
    {
        if (!ArrayToolkit::requireds($course, array('title', 'courseSetId', 'expiryMode', 'learnMode'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
        if (!in_array($course['learnMode'], array('freeMode', 'lockMode'))) {
            throw $this->createInvalidArgumentException('Param Invalid: LearnMode');
        }

        if (!isset($course['isDefault'])) {
            $course['isDefault'] = 0;
        }

        $course = ArrayToolkit::parts($course, array(
            'title',
            'about',
            'courseSetId',
            'learnMode',
            'expiryMode',
            'expiryDays',
            'expiryStartDate',
            'serializeMode',
            'expiryEndDate',
            'isDefault',
            'isFree',
            'serializeMode',
        ));

        if (!isset($course['isFree'])) {
            $course['isFree'] = 1; //默认免费
        }

        $course = $this->validateExpiryMode($course);

        $course['status'] = 'draft';
        $course['creator'] = $this->getCurrentUser()->getId();
        try {
            $this->beginTransaction();

            $created = $this->getCourseDao()->create($course);
            $currentUser = $this->getCurrentUser();
            //set default teacher
            $this->getMemberService()->setCourseTeachers($created['id'], array(
                array(
                    'id' => $currentUser['id'],
                    'isVisible' => 1,
                ),
            ));

            $this->commit();

            $this->dispatchEvent('course.create', new Event($created));

            return $created;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function copyCourse($fields)
    {
        $course = $this->tryManageCourse($fields['copyCourseId']);
        $fields = ArrayToolkit::parts($fields, array(
            'title',
            'courseSetId',
            'learnMode',
            'expiryMode',
            'expiryDays',
            'expiryStartDate',
            'expiryEndDate',
            'isDefault',
        ));
        $fields = $this->validateExpiryMode($fields);

        $entityCopy = new CourseCopy($this->biz);

        return $entityCopy->copy($course, $fields);
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
            'audiences',
            'enableFinish',
            'serializeMode',
            'maxStudentNum',
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
                throw $this->createInvalidArgumentException('Lack of required fields');
            }
        } elseif (!ArrayToolkit::requireds($course, array('title', 'courseSetId', 'expiryMode'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        } else {
            $fields = $this->validateExpiryMode($fields);
        }

        $course = $this->getCourseDao()->update($id, $fields);
        $this->dispatchEvent('course.update', new Event($course));

        return $course;
    }

    public function updateMaxRate($id, $maxRate)
    {
        $course = $this->getCourseDao()->update($id, array('maxRate' => $maxRate));
        $this->dispatchEvent('course.update', new Event($course));

        return $course;
    }

    public function updateCategoryByCourseSetId($courseSetId, $categoryId)
    {
        $this->getCourseDao()->updateCategoryByCourseSetId($courseSetId, array('categoryId' => $categoryId));
    }

    public function updateMaxRateByCourseSetId($courseSetId, $maxRate)
    {
        $course = $this->getCourseDao()->updateMaxRateByCourseSetId($courseSetId, array('updatedTime' => time(), 'maxRate' => $maxRate));

        return $course;
    }

    public function updateCourseMarketing($id, $fields)
    {
        $oldCourse = $this->tryManageCourse($id);

        $fields = ArrayToolkit::parts($fields, array(
            'isFree',
            'originPrice',
            'vipLevelId',
            'buyable',
            'tryLookable',
            'tryLookLength',
            'watchLimit',
            'buyExpiryTime',
            'services',
            'approval',
        ));

        $fields = $this->mergeCourseDefaultAttribute($fields);

        $fields['price'] = $this->calculatePrice($id, $fields['originPrice']);

        if (!ArrayToolkit::requireds($fields, array('isFree', 'buyable', 'tryLookable'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }

        if ($fields['isFree'] == 1) {
            $fields['price'] = 0;
            $fields['vipLevelId'] = 0;
        }

        if ($fields['tryLookable'] == 0) {
            $fields['tryLookLength'] = 0;
        }

        if (!empty($fields['buyExpiryTime'])) {
            $fields['buyExpiryTime'] = strtotime($fields['buyExpiryTime']);
        } else {
            $fields['buyExpiryTime'] = 0;
        }

        $newCourse = $this->getCourseDao()->update($id, $fields);

        $this->dispatchEvent('course.update', new Event($newCourse));
        $this->dispatchEvent('course.marketing.update', array('oldCourse' => $oldCourse, 'newCourse' => $newCourse));

        return $newCourse;
    }

    protected function calculatePrice($id, $originPrice)
    {
        return $originPrice;
    }

    public function updateCourseStatistics($id, $fields)
    {
        if (empty($fields)) {
            throw $this->createInvalidArgumentException('Invalid Arguments');
        }

        $updateFields = array();
        foreach ($fields as $field) {
            if ($field === 'studentNum') {
                $updateFields['studentNum'] = $this->countStudentsByCourseId($id);
            } elseif ($field === 'taskNum') {
                $updateFields['taskNum'] = $this->getTaskService()->countTasksByCourseId($id);
            } elseif ($field === 'publishedTaskNum') {
                $updateFields['publishedTaskNum'] = $this->getTaskService()->countTasks(array('courseId' => $id, 'status' => 'published'));
            } elseif ($field === 'threadNum') {
                $updateFields['threadNum'] = $this->countThreadsByCourseId($id);
            } elseif ($field === 'ratingNum') {
                $ratingFields = $this->getReviewService()->countRatingByCourseId($id);
                $updateFields = array_merge($updateFields, $ratingFields);
            } elseif ($field === 'noteNum') {
                $updateFields['noteNum'] = $this->getNoteService()->countCourseNoteByCourseId($id);
            } elseif ($field === 'materialNum') {
                $updateFields['materialNum'] = $this->getCourseMaterialService()->countMaterials(array('courseId' => $id, 'source' => 'coursematerial'));
            }
        }

        if (empty($updateFields)) {
            throw $this->createInvalidArgumentException('Invalid Arguments');
        }

        $course = $this->getCourseDao()->update($id, $updateFields);
        $this->dispatchEvent('course.update', new Event($course));

        return $course;
    }

    public function deleteCourse($id)
    {
        $course = $this->tryManageCourse($id);
        if ($course['status'] == 'published') {
            throw $this->createAccessDeniedException('Deleting published Course is not allowed');
        }
        $subCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($id, 1);
        if (!empty($subCourses)) {
            throw $this->createAccessDeniedException('至少需要保留一个教学计划，作为教学内容');
        }
        $courseCount = $this->getCourseDao()->count(array('courseSetId' => $course['courseSetId']));
        if ($courseCount <= 1) {
            throw $this->createAccessDeniedException('课程下至少需保留一个教学计划');
        }

        $result = $this->getCourseDeleteService()->deleteCourse($id);

        $this->dispatchEvent('course.delete', new Event($course));

        return $result;
    }

    public function closeCourse($id)
    {
        $course = $this->tryManageCourse($id);
        if ($course['status'] != 'published') {
            throw $this->createAccessDeniedException('Course has not bean published');
        }
        $course['status'] = 'closed';

        try {
            $this->beginTransaction();
            $course = $this->getCourseDao()->update($id, $course);

            $publishedCourses = $this->findPublishedCoursesByCourseSetId($course['courseSetId']);
            //如果课程下没有了已发布的教学计划，则关闭此课程
            if (empty($publishedCourses)) {
                $this->getCourseSetDao()->update($course['courseSetId'], array('status' => 'closed'));
            }
            $this->commit();
            $this->dispatchEvent('course.close', new Event($course));
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    public function publishCourse($id, $withTasks = false)
    {
        $this->tryManageCourse($id);
        $course = $this->getCourseDao()->update($id, array(
            'status' => 'published',
        ));
        $this->dispatchEvent('course.publish', $course);

        $this->getTaskService()->publishTasksByCourseId($id);
    }

    protected function validateExpiryMode($course)
    {
        if (empty($course['expiryMode'])) {
            return $course;
        }
        if ($course['expiryMode'] === 'days') {
            $course['expiryStartDate'] = null;
            $course['expiryEndDate'] = null;
        } elseif ($course['expiryMode'] === 'date') {
            $course['expiryDays'] = 0;
            if (isset($course['expiryStartDate'])) {
                $course['expiryStartDate'] = strtotime($course['expiryStartDate']);
            } else {
                throw $this->createInvalidArgumentException('Param Required: expiryStartDate');
            }
            if (isset($course['expiryEndDate'])) {
                $course['expiryEndDate'] = strtotime($course['expiryEndDate'].' 23:59:59');
            } else {
                throw $this->createInvalidArgumentException('Param Required: expiryEndDate');
            }
            if ($course['expiryEndDate'] <= $course['expiryStartDate']) {
                throw $this->createInvalidArgumentException('Value of Params expiryEndDate must later than expiryStartDate');
            }
        } else {
            throw $this->createInvalidArgumentException('Param Invalid: expiryMode');
        }

        return $course;
    }

    public function findCourseItems($courseId, $limitNum = 0)
    {
        $course = $this->getCourse($courseId);
        if (empty($course)) {
            throw $this->createNotFoundException("Course#{$courseId} Not Found");
        }
        $tasks = $this->findTasksByCourseId($course);

        return $this->createCourseStrategy($course)->prepareCourseItems($courseId, $tasks, $limitNum);
    }

    protected function findTasksByCourseId($course)
    {
        $user = $this->getCurrentUser();
        if ($user->isLogin()) {
            return $this->getTaskService()->findTasksFetchActivityAndResultByCourseId($course['id']);
        }

        return $this->getTaskService()->findTasksFetchActivityByCourseId($course['id']);
    }

    public function tryManageCourse($courseId, $courseSetId = 0)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('Unauthorized');
        }

        $course = $this->getCourseDao()->get($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException("Course#{$courseId} Not Found");
        }
        if ($courseSetId > 0 && $course['courseSetId'] !== $courseSetId) {
            throw $this->createInvalidArgumentException('Invalid Argument: Course#{$courseId} not in CoruseSet#{$courseSetId}');
        }
        if (!$this->hasCourseManagerRole($courseId)) {
            throw $this->createAccessDeniedException('Unauthorized');
        }

        return $course;
    }

    public function findStudentsByCourseId($courseId)
    {
        $students = $this->getMemberDao()->findByCourseIdAndRole($courseId, 'student');

        return $this->fillMembersWithUserInfo($students);
    }

    public function findTeachersByCourseId($courseId)
    {
        $teachers = $this->getMemberDao()->findByCourseIdAndRole($courseId, 'teacher');

        return $this->fillMembersWithUserInfo($teachers);
    }

    public function countStudentsByCourseId($courseId)
    {
        return $this->getMemberDao()->count(array(
            'courseId' => $courseId,
            'role' => 'student',
        ));
    }

    public function countThreadsByCourseId($courseId)
    {
        return $this->getThreadDao()->count(array(
            'courseId' => $courseId,
        ));
    }

    public function getUserRoleInCourse($courseId, $userId)
    {
        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);

        return empty($member) ? null : $member['role'];
    }

    public function findUserTeachingCoursesByCourseSetId($courseSetId, $onlyPublished = true)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $members = $this->getMemberService()->findTeacherMembersByUserIdAndCourseSetId($user['id'], $courseSetId);
        $ids = ArrayToolkit::column($members, 'courseId');
        if ($onlyPublished) {
            return $this->findPublicCoursesByIds($ids);
        } else {
            return $this->findCoursesByIds($ids);
        }
    }

    public function findPriceIntervalByCourseSetIds($courseSetIds)
    {
        $results = $this->getCourseDao()->findPriceIntervalByCourseSetIds($courseSetIds);

        return ArrayToolkit::index($results, 'courseSetId');
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
        $user = $this->getCurrentUser();
        $member = $this->getMemberDao()->getByCourseIdAndUserId($course['id'], $user['id']);

        return array($course, $member);
    }

    public function canTakeCourse($course)
    {
        $course = !is_array($course) ? $this->getCourse(intval($course)) : $course;

        if (empty($course)) {
            return false;
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }

        if ($user->hasPermission('admin_course_set')) {
            return true;
        }

        //TODO 未实现
        //        if ($course['parentId'] && $this->isClassroomMember($course, $user['id'])) {
        //            return true;
        //        }

        $member = $this->getMemberDao()->getByCourseIdAndUserId($course['id'], $user['id']);

        if ($member && in_array($member['role'], array('teacher', 'student'))) {
            return true;
        }

        return false;
    }

    public function sortCourseItems($courseId, $ids)
    {
        $course = $this->tryManageCourse($courseId);

        $this->createCourseStrategy($course)->sortCourseItems($courseId, $ids);
    }

    public function createChapter($chapter)
    {
        if (!in_array($chapter['type'], array('chapter', 'unit', 'lesson'))) {
            throw $this->createInvalidArgumentException('Invalid Chapter Type');
        }

        if (in_array($chapter['type'], array('unit', 'lesson'))) {
            list($chapter['number'], $chapter['parentId']) = $this->getNextNumberAndParentId($chapter['courseId']);
        } else {
            $chapter['number'] = $this->getNextChapterNumber($chapter['courseId']);
            $chapter['parentId'] = 0;
        }

        $chapter['seq'] = $this->getNextCourseItemSeq($chapter['courseId']);
        $chapter['createdTime'] = time();

        $chapter = $this->getChapterDao()->create($chapter);

        $this->dispatchEvent('course.chapter.create', new Event($chapter));

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
        $taskMaxSeq = $this->getTaskService()->getMaxSeqByCourseId($courseId);

        return ($chapterMaxSeq > $taskMaxSeq ? $chapterMaxSeq : $taskMaxSeq) + 1;
    }

    public function updateChapter($courseId, $chapterId, $fields)
    {
        $this->tryManageCourse($courseId);
        $chapter = $this->getChapterDao()->get($chapterId);

        if (empty($chapter) || $chapter['courseId'] != $courseId) {
            throw $this->createNotFoundException("Chapter#{$chapterId} Not Found");
        }

        $fields = ArrayToolkit::parts($fields, array('title', 'number', 'seq', 'parentId'));

        $chapter = $this->getChapterDao()->update($chapterId, $fields);
        $this->dispatchEvent('course.chapter.update', new Event($chapter));

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
        $this->dispatchEvent('course.chapter.delete', new Event($deletedChapter));

        $prevChapter = array('id' => 0);

        foreach ($this->getChapterDao()->findChaptersByCourseId($course['id']) as $chapter) {
            if ($chapter['number'] < $deletedChapter['number']) {
                $prevChapter = $chapter;
            }
        }

        $tasks = $this->getTaskService()->findTasksByChapterId($deletedChapter['id']);

        foreach ($tasks as $task) {
            $this->getTaskService()->updateSeq($task['id'], array('categoryId' => $prevChapter['id']));
        }
    }

    public function getChapter($courseId, $chapterId)
    {
        $chapter = $this->getChapterDao()->get($chapterId);
        $course = $this->getCourseDao()->get($courseId);
        if ($course['id'] == $chapter['courseId']) {
            return $chapter;
        }

        return array();
    }

    public function countUserLearningCourses($userId, $filters = array())
    {
        $conditions = $this->prepareUserLearnCondition($userId, $filters);

        return $this->getMemberDao()->countLearningMembers($conditions);
    }

    public function findUserLearningCourses($userId, $start, $limit, $filters = array())
    {
        $conditions = $this->prepareUserLearnCondition($userId, $filters);

        $members = $this->getMemberDao()->findLearningMembers($conditions, $start, $limit);

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));
        $courses = ArrayToolkit::index($courses, 'id');

        $sortedCourses = array();

        foreach ($members as $member) {
            if (empty($courses[$member['courseId']])) {
                continue;
            }

            $course = $courses[$member['courseId']];
            $course['memberIsLearned'] = 0;
            $course['memberLearnedNum'] = $member['learnedNum'];
            $sortedCourses[] = $course;
        }

        return $sortedCourses;
    }

    public function countUserLearnedCourses($userId, $filters = array())
    {
        $conditions = $this->prepareUserLearnCondition($userId, $filters);

        return $this->getMemberDao()->countLearnedMembers($conditions);
    }

    public function findUserLearnedCourses($userId, $start, $limit, $filters = array())
    {
        $conditions = $this->prepareUserLearnCondition($userId, $filters);
        $members = $this->getMemberDao()->findLearnedMembers($conditions, $start, $limit);

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));
        $courses = ArrayToolkit::index($courses, 'id');

        $sortedCourses = array();

        foreach ($members as $member) {
            if (empty($courses[$member['courseId']])) {
                continue;
            }

            $course = $courses[$member['courseId']];
            $course['memberIsLearned'] = 1;
            $course['memberLearnedNum'] = $member['learnedNum'];
            $sortedCourses[] = $course;
        }

        return $sortedCourses;
    }

    public function findUserTeachCourseCount($conditions, $onlyPublished = true)
    {
        $members = $this->getMemberDao()->findByUserIdAndRole($conditions['userId'], 'teacher');
        unset($conditions['userId']);

        if (!$members) {
            return 0;
        }

        $conditions['courseIds'] = ArrayToolkit::column($members, 'courseId');

        if ($onlyPublished) {
            $conditions['status'] = 'published';
        }

        return $this->searchCourseCount($conditions);
    }

    public function findUserTeachCourses($conditions, $start, $limit, $onlyPublished = true)
    {
        $members = $this->getMemberDao()->findByUserIdAndRole($conditions['userId'], 'teacher');
        unset($conditions['userId']);

        if (!$members) {
            return array();
        }

        $conditions['courseIds'] = ArrayToolkit::column($members, 'courseId');

        if ($onlyPublished) {
            $conditions['status'] = 'published';
        }

        return $this->searchCourses($conditions, array('createdTime' => 'DESC'), $start, $limit);
    }

    public function findLearnedCoursesByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getMemberDao()->findLearnedByCourseIdAndUserId($courseId, $userId);
    }

    public function findTeachingCoursesByUserId($userId, $onlyPublished = true)
    {
        $members = $this->getMemberService()->findTeacherMembersByUserId($userId);
        $courseIds = ArrayToolkit::column($members, 'courseId');
        if ($onlyPublished) {
            $courses = $this->findPublicCoursesByIds($courseIds);
        } else {
            $courses = $this->findCoursesByIds($courseIds);
        }

        return $courses;
    }

    public function findUserLearnCourses($userId, $start, $limit)
    {
        return $this->getTaskService()->searchMembers(array('userId' => $userId), array(), $start, $limit);
    }

    public function countUserLearnCourse($userId)
    {
        return $this->getMemberService()->countMembers(array('userId' => $userId));
    }

    /**
     * @param int $userId
     *
     * @return mixed
     */
    public function findLearnCoursesByUserId($userId)
    {
        $members = $this->getMemberService()->findStudentMemberByUserId($userId);
        $courseIds = ArrayToolkit::column($members, 'courseId');
        $courses = $this->findPublicCoursesByIds($courseIds);

        return $courses;
    }

    public function findPublicCoursesByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $conditions = array(
            'status' => 'published',
            'courseIds' => $ids,
        );
        $count = $this->searchCourseCount($conditions);

        return $this->searchCourses($conditions, array('createdTime' => 'DESC'), 0, $count);
    }

    public function hasCourseManagerRole($courseId = 0)
    {
        $user = $this->getCurrentUser();
        //未登录，无权限管理
        if (!$user->isLogin()) {
            return false;
        }

        //不是管理员，无权限管理
        if ($this->hasAdminRole()) {
            return true;
        }

        $course = $this->getCourse($courseId);
        //课程不存在，无权限管理
        if (empty($course)) {
            return false;
        }

        if ($course['creator'] == $user->getId()) {
            return true;
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        if ($user->getId() == $courseSet['creator']) {
            return true;
        }

        $teacher = $this->getMemberService()->isCourseTeacher($courseId, $user->getId());
        //不是课程教师，无权限管理
        if ($teacher) {
            return true;
        }

        if ($course['parentId'] > 0) {
            $classrooms = $this->getClassroomService()->findClassroomIdsByCourseId($course['id']);

            $isTeacher = $this->getClassroomService()->isClassroomTeacher($classrooms[0]['classroomId'], $user['id']);
            $isHeadTeacher = $this->getClassroomService()->isClassroomHeadTeacher($classrooms[0]['classroomId'], $user['id']);
            if ($isTeacher || $isHeadTeacher) {
                return true;
            }
        }

        return false;
    }

    public function analysisCourseDataByTime($startTime, $endTime)
    {
        return $this->getCourseDao()->analysisCourseDataByTime($startTime, $endTime);
    }

    protected function fillMembersWithUserInfo($members)
    {
        if (empty($members)) {
            return $members;
        }

        $userIds = ArrayToolkit::column($members, 'userId');
        $user = $this->getUserService()->findUsersByIds($userIds);
        $userMap = ArrayToolkit::index($user, 'id');
        foreach ($members as $index => $member) {
            $member['nickname'] = $userMap[$member['userId']]['nickname'];
            $member['smallAvatar'] = $userMap[$member['userId']]['smallAvatar'];
            $members[$index] = $member;
        }

        return $members;
    }

    protected function _prepareCourseConditions($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if ($value == 0) {
                return true;
            }

            return !empty($value);
        });

        if (isset($conditions['date'])) {
            $dates = array(
                'yesterday' => array(
                    strtotime('yesterday'),
                    strtotime('today'),
                ),
                'today' => array(
                    strtotime('today'),
                    strtotime('tomorrow'),
                ),
                'this_week' => array(
                    strtotime('Monday this week'),
                    strtotime('Monday next week'),
                ),
                'last_week' => array(
                    strtotime('Monday last week'),
                    strtotime('Monday this week'),
                ),
                'next_week' => array(
                    strtotime('Monday next week'),
                    strtotime('Monday next week', strtotime('Monday next week')),
                ),
                'this_month' => array(
                    strtotime('first day of this month midnight'),
                    strtotime('first day of next month midnight'),
                ),
                'last_month' => array(
                    strtotime('first day of last month midnight'),
                    strtotime('first day of this month midnight'),
                ),
                'next_month' => array(
                    strtotime('first day of next month midnight'),
                    strtotime('first day of next month midnight', strtotime('first day of next month midnight')),
                ),
            );

            if (array_key_exists($conditions['date'], $dates)) {
                $conditions['startTimeGreaterThan'] = $dates[$conditions['date']][0];
                $conditions['startTimeLessThan'] = $dates[$conditions['date']][1];
                unset($conditions['date']);
            }
        }

        if (isset($conditions['creator']) && !empty($conditions['creator'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['creator']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['creator']);
        }

        if (isset($conditions['categoryId'])) {
            $conditions['categoryIds'] = array();

            if (!empty($conditions['categoryId'])) {
                $childrenIds = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
                $conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
            }

            unset($conditions['categoryId']);
        }

        if (isset($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['nickname']);
        }

        return $conditions;
    }

    public function searchCourses($conditions, $sort, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);
        $orderBy = $this->_prepareCourseOrderBy($sort);

        return $this->getCourseDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function getMinPublishedCoursePriceByCourseSetId($courseSetId)
    {
        return $this->getCourseDao()->getMinPublishedCoursePriceByCourseSetId($courseSetId);
    }

    public function getMinAndMaxPublishedCoursePriceByCourseSetId($courseSetId)
    {
        return $this->getCourseDao()->getMinAndMaxPublishedCoursePriceByCourseSetId($courseSetId);
    }

    public function findUserLearningCourseCountNotInClassroom($userId, $filters = array())
    {
        if (isset($filters['type'])) {
            return $this->getMemberDao()->countMemberNotInClassroomByUserIdAndCourseTypeAndIsLearned($userId, 'student', $filters['type'], 0);
        }

        return $this->getMemberDao()->countMemberNotInClassroomByUserIdAndRoleAndIsLearned($userId, 'student', 0);
    }

    public function findUserLearningCoursesNotInClassroom($userId, $start, $limit, $filters = array())
    {
        if (isset($filters['type'])) {
            $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndCourseTypeAndIsLearned($userId, 'student', $filters['type'], '0', $start, $limit);
        } else {
            $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndRoleAndIsLearned($userId, 'student', 0, $start, $limit);
        }

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

        $sortedCourses = array();

        foreach ($members as $member) {
            if (empty($courses[$member['courseId']])) {
                continue;
            }

            $course = $courses[$member['courseId']];
            $course['memberIsLearned'] = 0;
            $course['memberLearnedNum'] = $member['learnedNum'];
            $sortedCourses[] = $course;
        }

        return $sortedCourses;
    }

    public function findUserLeanedCourseCount($userId, $filters = array())
    {
        if (isset($filters['type'])) {
            return $this->getMemberDao()->countMemberByUserIdAndCourseTypeAndIsLearned($userId, 'student', $filters['type'], 1);
        }

        return $this->getMemberDao()->countMemberByUserIdAndRoleAndIsLearned($userId, 'student', 1);
    }

    public function findUserLearnedCoursesNotInClassroom($userId, $start, $limit, $filters = array())
    {
        if (isset($filters['type'])) {
            $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndCourseTypeAndIsLearned($userId, 'student', $filters['type'], 1, $start, $limit);
        } else {
            $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndRoleAndIsLearned($userId, 'student', 1, $start, $limit);
        }

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

        $sortedCourses = array();

        foreach ($members as $member) {
            if (empty($courses[$member['courseId']])) {
                continue;
            }

            $course = $courses[$member['courseId']];
            $course['memberIsLearned'] = 1;
            $course['memberLearnedNum'] = $member['learnedNum'];
            $sortedCourses[] = $course;
        }

        return $sortedCourses;
    }

    public function findUserLearnCourseCountNotInClassroom($userId, $onlyPublished = true)
    {
        return $this->getMemberDao()->countMemberNotInClassroomByUserIdAndRole($userId, 'student', $onlyPublished);
    }

    public function findUserLearnCoursesNotInClassroom($userId, $start, $limit, $onlyPublished = true)
    {
        $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndRole($userId, 'student', $start, $limit, $onlyPublished);

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

        return $courses;
    }

    public function findUserLearnCoursesNotInClassroomWithType($userId, $type, $start, $limit, $onlyPublished = true)
    {
        $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndRoleAndType($userId, 'student', $type, $start, $limit, $onlyPublished);

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

        return $courses;
    }

    public function findUserTeachCourseCountNotInClassroom($conditions, $onlyPublished = true)
    {
        $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndRole($conditions['userId'], 'teacher', 0, PHP_INT_MAX, $onlyPublished);
        unset($conditions['userId']);

        $courseIds = ArrayToolkit::column($members, 'courseId');
        $conditions['courseIds'] = $courseIds;

        if (count($courseIds) == 0) {
            return 0;
        }

        if ($onlyPublished) {
            $conditions['status'] = 'published';
        }

        return $this->searchCourseCount($conditions);
    }

    public function findUserTeachCoursesNotInClassroom($conditions, $start, $limit, $onlyPublished = true)
    {
        $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndRole($conditions['userId'], 'teacher', $start, $limit, $onlyPublished);
        unset($conditions['userId']);

        $courseIds = ArrayToolkit::column($members, 'courseId');
        $conditions['courseIds'] = $courseIds;

        if (count($courseIds) == 0) {
            return array();
        }

        if ($onlyPublished) {
            $conditions['status'] = 'published';
        }

        $courses = $this->searchCourses($conditions, 'latest', 0, PHP_INT_MAX);

        return $courses;
    }

    public function findUserFavoritedCourseCountNotInClassroom($userId)
    {
        $courseFavorites = $this->getFavoriteDao()->findCourseFavoritesNotInClassroomByUserId($userId, 0, PHP_INT_MAX);
        $courseIds = ArrayToolkit::column($courseFavorites, 'courseId');
        $conditions = array('courseIds' => $courseIds);
        if (count($courseIds) == 0) {
            return 0;
        }

        return $this->searchCourseCount($conditions);
    }

    public function findUserFavoritedCoursesNotInClassroom($userId, $start, $limit)
    {
        $courseFavorites = $this->getFavoriteDao()->findCourseFavoritesNotInClassroomByUserId($userId, $start, $limit);

        return $this->getCourseDao()->findCoursesByIds(ArrayToolkit::column($courseFavorites, 'courseId'));
    }

    protected function _prepareCourseOrderBy($sort)
    {
        if (is_array($sort)) {
            $orderBy = $sort;
        } elseif ($sort == 'popular' || $sort == 'hitNum') {
            $orderBy = array('hitNum' => 'DESC');
        } elseif ($sort == 'recommended') {
            $orderBy = array('recommendedTime' => 'DESC');
        } elseif ($sort == 'Rating') {
            $orderBy = array('Rating' => 'DESC');
        } elseif ($sort == 'studentNum') {
            $orderBy = array('studentNum' => 'DESC');
        } elseif ($sort == 'recommendedSeq') {
            $orderBy = array('recommendedSeq' => 'ASC', 'recommendedTime' => 'DESC');
        } elseif ($sort == 'createdTimeByAsc') {
            $orderBy = array('createdTime' => 'ASC');
        } else {
            $orderBy = array('createdTime' => 'DESC');
        }

        return $orderBy;
    }

    public function searchCourseCount($conditions)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getCourseDao()->count($conditions);
    }

    public function countCourses(array $conditions)
    {
        return $this->getCourseDao()->count($conditions);
    }

    protected function createCourseStrategy($course)
    {
        return StrategyContext::getInstance()->createStrategy($course['isDefault'], $this->biz);
    }

    public function calculateLearnProgressByUserIdAndCourseIds($userId, array $courseIds)
    {
        if (empty($userId) || empty($courseIds)) {
            return array();
        }
        $courses = $this->findCoursesByIds($courseIds);

        $conditions = array(
            'courseIds' => $courseIds,
            'userId' => $userId
        );
        $count = $this->getMemberService()->countMembers($conditions);
        $members = $this->getMemberService()->searchMembers(
            $conditions,
            array('id' => 'DESC'),
            0,
            $count
        );

        $learnProgress = array();
        foreach ($members as $member) {
            $learnProgress[] = array(
                'courseId' => $member['courseId'],
                'totalLesson' => $courses[$member['courseId']]['taskNum'],
                'learnedNum' => $member['learnedNum']
            );
        }

        return $learnProgress;
    }

    protected function hasAdminRole()
    {
        $user = $this->getCurrentUser();

        return $user->hasPermission('admin_course_content_manage');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return CourseMemberDaoImpl
     */
    protected function getMemberDao()
    {
        return $this->createDao('Course:CourseMemberDao');
    }

    /**
     * @return CourseChapterDao
     */
    protected function getChapterDao()
    {
        return $this->createDao('Course:CourseChapterDao');
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }

    /**
     * @return CourseSetDao
     */
    protected function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }

    /**
     * @return ThreadDao
     */
    protected function getThreadDao()
    {
        return $this->createDao('Course:ThreadDao');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return MaterialService
     */
    protected function getCourseMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->createService('Course:ReviewService');
    }

    /**
     * @return CourseNoteService
     */
    protected function getNoteService()
    {
        return $this->createService('Course:CourseNoteService');
    }

    /**
     * @return CourseDeleteService
     */
    protected function getCourseDeleteService()
    {
        return $this->createService('Course:CourseDeleteService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return FavoriteDaoImpl
     */
    protected function getFavoriteDao()
    {
        return $this->createDao('Course:FavoriteDao');
    }

    /**
     * 当默认值未设置时，合并默认值
     *
     * @param  $course
     *
     * @return array
     */
    protected function mergeCourseDefaultAttribute($course)
    {
        $course = array_filter($course, function ($value) {
            if ($value === '' || $value === null) {
                return false;
            }

            return true;
        });

        $default = array(
            'tryLookable' => 0,
            'originPrice' => 0.00,
        );

        return array_merge($default, $course);
    }

    /**
     * used for search userLearn userLearning userLearned.
     *
     * @param  $userId
     * @param  $filters
     *
     * @return array
     */
    protected function prepareUserLearnCondition($userId, $filters)
    {
        $filters = ArrayToolkit::parts($filters, array('type', 'classroomId', 'locked'));
        $conditions = array(
            'm.userId' => $userId,
            'm.role' => 'student',
        );
        if (!empty($filters['type'])) {
            $conditions['c.type'] = $filters['type'];
        }
        if (!empty($filters['classroomId'])) {
            $conditions['m.classroomId'] = $filters['classroomId'];
        }

        if (!empty($filters['locked'])) {
            $conditions['m.locked'] = $filters['locked'];
        }

        return $conditions;
    }
}
