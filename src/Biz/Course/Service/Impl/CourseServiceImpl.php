<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\ThreadDao;
use Topxia\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Service\CourseService;
use Biz\Task\Strategy\StrategyContext;
use Codeages\Biz\Framework\Event\Event;
use Biz\Taxonomy\Service\CategoryService;

class CourseServiceImpl extends BaseService implements CourseService
{
    public function getCourse($id)
    {
        return $this->getCourseDao()->get($id);
    }

    public function findCoursesByIds($ids)
    {
        return $this->getCourseDao()->findCoursesByIds($ids);
    }

    public function findCoursesByCourseSetId($courseSetId)
    {
        return $this->getCourseDao()->findCoursesByCourseSetId($courseSetId);
    }

    public function getDefaultCourseByCourseSetId($courseSetId)
    {
        return $this->getCourseDao()->getDefaultCourseByCourseSetId($courseSetId);
    }

    public function getFirstPublishedCourseByCourseSetId($courseSetId)
    {
        return $this->getCourseDao()->getFirstPublishedByCourseSetId($courseSetId);
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
        if (!in_array($course['learnMode'], array('freeMode', 'lockMode'))) {
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

    public function setCourseTeachers($courseId, $teachers)
    {
        $teacherMembers = array();

        foreach (array_values($teachers) as $index => $teacher) {
            if (empty($teacher['id'])) {
                throw $this->createInvalidArgumentException('Teacher ID Required');
            }

            $user = $this->getUserService()->getUser($teacher['id']);

            if (empty($user)) {
                throw $this->createInvalidArgumentException('No Such Teacher');
            }

            $teacherMembers[] = array(
                'courseId'  => $courseId,
                'userId'    => $user['id'],
                'role'      => 'teacher',
                'seq'       => $index,
                'isVisible' => empty($teacher['isVisible']) ? 0 : 1
            );
        }

        $existTeachers = $this->findMembersByCourseIdAndRole($courseId, 'teacher');

        foreach ($existTeachers as $member) {
            $this->getMemberDao()->delete($member['id']);
        }

        $visibleTeacherIds = array();

        foreach ($teacherMembers as $member) {
            $existMember = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $member['userId']);

            if ($existMember) {
                $this->getMemberDao()->delete($existMember['id']);
            }

            $member = $this->getMemberDao()->create($member);

            if ($member['isVisible']) {
                $visibleTeacherIds[] = $member['userId'];
            }
        }

        $fields = array('teacherIds' => $visibleTeacherIds);
        $course = $this->getCourseDao()->update($courseId, $fields);
    }

    public function updateCourseMarketing($id, $fields)
    {
        $this->tryManageCourse($id);
        $fields = ArrayToolkit::parts($fields, array(
            'isFree',
            'price',
            'vipLevelId',
            'buyable',
            'tryLookable',
            'tryLookLength',
            'watchLimit',
            'services'
        ));

        if (!ArrayToolkit::requireds($fields, array('isFree', 'buyable', 'tryLookable'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
        if ($fields['isFree'] == 1) {
            $fields['price']      = 0;
            $fields['vipLevelId'] = 0;
        }
        if ($fields['tryLookable'] == 0) {
            $fields['tryLookLength'] = 0;
        }
        if (isset($fields['price'])) {
            $fields['price'] = round(floatval($fields['price']) * 100, 0);
        }

        return $this->getCourseDao()->update($id, $fields);
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
            } elseif ($field === 'threadNum') {
                $updateFields['threadNum'] = $this->countThreadsByCourseId($id);
            }
        }

        if (empty($updateFields)) {
            throw $this->createInvalidArgumentException('Invalid Arguments');
        }

        return $this->getCourseDao()->update($id, $updateFields);
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
        $this->tryManageCourse($id);
        $this->getCourseDao()->update($id, array(
            'status' => 'published'
        ));
        // $this->dispatchEvent('course.publish', $course);
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
        if (empty($course)) {
            throw $this->createNotFoundException("Course#{$courseId} Not Found");
        }
        $tasks = $this->findTasksByCourseId($courseId);
        return $this->createCourseStrategy($course)->prepareCourseItems($courseId, $tasks);
    }

    protected function findTasksByCourseId($courseId)
    {
        $user = $this->getCurrentUser();
        if ($user->isLogin()) {
            return $this->getTaskService()->findTasksFetchActivityAndResultByCourseId($courseId);
        } else {
            return $this->getTaskService()->findTasksFetchActivityByCourseId($courseId);
        }
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
            'role'     => 'student'
        ));
    }

    public function countThreadsByCourseId($courseId)
    {
        return $this->getThreadDao()->count(array(
            'courseId' => $courseId
        ));
    }

    public function createCourseStudent($courseId, $fields)
    {
        $this->tryManageCourse($courseId);
        if (!ArrayToolkit::requireds($fields, array('userId', 'price'))) {
            throw $this->createInvalidArgumentException("Lack of required fields");
        }
        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $fields['userId']);
        if (!empty($member)) {
            throw $this->createInvalidArgumentException("User#{$fields['userId']} is already in Course#{$courseId}");
        }
        $fields = ArrayToolkit::parts($fields, array(
            'userId',
            // 'price', // create order ...
            'remark'
        ));

        $fields['role']        = 'student';
        $fields['joinedType']  = 'course';
        $fields['classroomId'] = 0;
        $fields['courseId']    = $courseId;

        //TODO create order

        $result = $this->getMemberDao()->create($fields);
        $this->biz['dispatcher']->dispatch("course.student.create", new Event($result));
        return $result;
    }

    public function removeCourseStudent($courseId, $userId)
    {
        $this->tryManageCourse($courseId);
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            throw $this->createNotFoundException("User#{$user['id']} Not Found");
        }
        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);
        if (empty($member)) {
            throw $this->createNotFoundException("User#{$user['id']} Not in Course#{$courseId}");
        }
        if ($member['role'] !== 'student') {
            throw $this->createInvalidArgumentException("User#{$user['id']} is Not a Student of Course#{$courseId}");
        }
        $result = $this->getMemberDao()->delete($member['id']);

        $this->biz['dispatcher']->dispatch("course.student.delete", new Event($member));
        return $result;
    }

    public function getUserRoleInCourse($courseId, $userId)
    {
        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);
        return empty($member) ? null : $member['role'];
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

        if ($user->hasPermission('admin_course')) {
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

    //TODO 任务需要在排序时处理 chapterId， number
    public function sortCourseItems($courseId, $ids)
    {
        $course = $this->tryManageCourse($courseId);

        $this->createCourseStrategy($course)->sortCourseItems($courseId, $ids);
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
        $this->tryManageCourse($courseId);
        $chapter = $this->getChapterDao()->get($chapterId);

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
            $this->getTaskService()->updateTask($task['id'], array('categoryId' => $prevChapter['id']));
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

    public function findUserLeaningCourseCount($userId, $filters = array())
    {
        $conditions = array(
            'userId'    => $userId,
            'role'      => 'student',
            'isLearned' => 0

        );
        if (isset($filters["type"])) {
            $conditions['type'] = $filters["type"];
            return $this->getMemberDao()->countMemberFetchCourse($conditions);
        }
        return $this->getMemberDao()->count($conditions);
    }

    public function findUserLeaningCourses($userId, $start, $limit, $filters = array('type' => ''))
    {
        $conditions = array(
            'userId'    => $userId,
            'role'      => 'student',
            'isLearned' => 0
        );
        if (isset($filters["type"])) {
            $conditions['type'] = $filters["type"];
            $members            = $this->getMemberDao()->searchMemberFetchCourse($conditions, array('createdTime' => 'DESC'), $start, $limit);
        } else {
            $members = $this->getMemberDao()->search($conditions, array(), $start, $limit);
        }

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

        $sortedCourses = array();

        foreach ($members as $member) {
            if (empty($courses[$member['courseId']])) {
                continue;
            }

            $course                     = $courses[$member['courseId']];
            $course['memberIsLearned']  = 0;
            $course['memberLearnedNum'] = $member['learnedNum'];
            $sortedCourses[]            = $course;
        }

        return $sortedCourses;
    }

    public function findUserLeanedCourseCount($userId, $filters = array())
    {
        $conditions = array(
            'userId'    => $userId,
            'role'      => 'student',
            'isLearned' => 1

        );
        if (isset($filters["type"])) {
            $conditions['type'] = $filters["type"];
            return $this->getMemberDao()->countMemberFetchCourse($conditions);
        }
        return $this->getMemberDao()->count($conditions);
    }

    public function findLearnedCoursesByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getMemberDao()->findLearnedByCourseIdAndUserId($courseId, $userId);
    }

    public function hasCourseManagerRole($courseId = 0)
    {
        $userId = $this->getCurrentUser()->getId();
        //TODO
        //1. courseId为空，判断是否有创建教学计划的权限
        //2. courseId不为空，判断是否有该教学计划的管理权限
        return true;
    }

    protected function fillMembersWithUserInfo($members)
    {
        if (empty($members)) {
            return $members;
        }

        $userIds = ArrayToolkit::column($members, 'userId');
        $user    = $this->getUserService()->findUsersByIds($userIds);
        $userMap = ArrayToolkit::index($user, 'id');
        foreach ($members as $index => $member) {
            $member['nickname']    = $userMap[$member['userId']]['nickname'];
            $member['smallAvatar'] = $userMap[$member['userId']]['smallAvatar'];
            $members[$index]       = $member;
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
                'yesterday'  => array(
                    strtotime('yesterday'),
                    strtotime('today')
                ),
                'today'      => array(
                    strtotime('today'),
                    strtotime('tomorrow')
                ),
                'this_week'  => array(
                    strtotime('Monday this week'),
                    strtotime('Monday next week')
                ),
                'last_week'  => array(
                    strtotime('Monday last week'),
                    strtotime('Monday this week')
                ),
                'next_week'  => array(
                    strtotime('Monday next week'),
                    strtotime('Monday next week', strtotime('Monday next week'))
                ),
                'this_month' => array(
                    strtotime('first day of this month midnight'),
                    strtotime('first day of next month midnight')
                ),
                'last_month' => array(
                    strtotime('first day of last month midnight'),
                    strtotime('first day of this month midnight')
                ),
                'next_month' => array(
                    strtotime('first day of next month midnight'),
                    strtotime('first day of next month midnight', strtotime('first day of next month midnight'))
                )
            );

            if (array_key_exists($conditions['date'], $dates)) {
                $conditions['startTimeGreaterThan'] = $dates[$conditions['date']][0];
                $conditions['startTimeLessThan']    = $dates[$conditions['date']][1];
                unset($conditions['date']);
            }
        }

        if (isset($conditions['creator']) && !empty($conditions['creator'])) {
            $user                 = $this->getUserService()->getUserByNickname($conditions['creator']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['creator']);
        }

        if (isset($conditions['categoryId'])) {
            $conditions['categoryIds'] = array();

            if (!empty($conditions['categoryId'])) {
                $childrenIds               = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
                $conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
            }

            unset($conditions['categoryId']);
        }

        if (isset($conditions['nickname'])) {
            $user                 = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['nickname']);
        }

        return $conditions;
    }

    public function searchCourses($conditions, $sort, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);
        $orderBy    = $this->_prepareCourseOrderBy($sort);

        return $this->getCourseDao()->search($conditions, $orderBy, $start, $limit);
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

    public function waveNoteNum($courseId, $num)
    {
        return $this->getCourseDao()->wave(array($courseId), array(
            'noteNum' => $num
        ));
    }

    protected function createCourseStrategy($course)
    {
        return StrategyContext::getInstance()->createStrategy($course['isDefault'], $this->biz);
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return CourseMemberDao
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
     * @return ThreadDao
     */
    protected function getThreadDao()
    {
        return $this->createDao('Course:ThreadDao');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->biz->service('Taxonomy:CategoryService');
    }
}
