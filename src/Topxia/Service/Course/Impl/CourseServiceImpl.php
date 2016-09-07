<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Course\CourseService;
use Topxia\Service\Util\EdusohoLiveClient;

class CourseServiceImpl extends BaseService implements CourseService
{
    /**
     * Course API
     */

    public function findCoursesByIds(array $ids)
    {
        $courses = CourseSerialize::unserializes(
            $this->getCourseDao()->findCoursesByIds($ids)
        );
        return ArrayToolkit::index($courses, 'id');
    }

    public function findCoursesByParentIdAndLocked($parentId, $locked)
    {
        return $this->getCourseDao()->findCoursesByParentIdAndLocked($parentId, $locked);
    }

    public function findCoursesByCourseIds(array $ids, $start, $limit)
    {
        $courses = CourseSerialize::unserializes(
            $this->getCourseDao()->findCoursesByCourseIds($ids, $start, $limit)
        );
        return ArrayToolkit::index($courses, 'id');
    }

    public function findCoursesByLikeTitle($title)
    {
        $coursesUnserialized = $this->getCourseDao()->findCoursesByLikeTitle($title);
        $courses             = CourseSerialize::unserializes($coursesUnserialized);
        return ArrayToolkit::index($courses, 'id');
    }

    // todo 和searchCourses合并
    public function findNormalCoursesByAnyTagIdsAndStatus(array $tagIds, $status, $orderBy, $start, $limit)
    {
        $courses = CourseSerialize::unserializes(
            $this->getCourseDao()->findNormalCoursesByAnyTagIdsAndStatus($tagIds, $status, $orderBy, $start, $limit)
        );
        return ArrayToolkit::index($courses, 'id');
    }

    public function findMinStartTimeByCourseId($courseId)
    {
        return $this->getLessonDao()->findMinStartTimeByCourseId($courseId);
    }

    public function getLesson($id)
    {
        $lesson       = $this->getLessonDao()->getLesson($id);
        $lessonExtend = $this->getLessonExtendDao()->getLesson($id);

        if ($lessonExtend) {
            return array_merge($lesson, $lessonExtend);
        } else {
            return $lesson;
        }
    }

    public function findLessonsByIds(array $ids)
    {
        $lessons = $this->getLessonDao()->findLessonsByIds($ids);
        $lessons = LessonSerialize::unserializes($lessons);
        return ArrayToolkit::index($lessons, 'id');
    }

    public function findLessonsByCopyIdAndLockedCourseIds($copyId, array $courseIds)
    {
        return $this->getLessonDao()->findLessonsByCopyIdAndLockedCourseIds($copyId, $courseIds);
    }

    public function getCourse($id, $inChanging = false)
    {
        return CourseSerialize::unserialize($this->getCourseDao()->getCourse($id));
    }

    // TODO searchCoursesCount
    public function findCoursesCountByLessThanCreatedTime($endTime)
    {
        return $this->getCourseDao()->findCoursesCountByLessThanCreatedTime($endTime);
    }

    public function analysisCourseSumByTime($endTime)
    {
        return $this->getCourseDao()->analysisCourseSumByTime($endTime);
    }

    public function searchCourses($conditions, $sort, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        if (is_array($sort)) {
            $orderBy = $sort;
        } elseif ($sort == 'popular' || $sort == 'hitNum') {
            $orderBy = array('hitNum', 'DESC');
        } elseif ($sort == 'recommended') {
            $orderBy = array('recommendedTime', 'DESC');
        } elseif ($sort == 'Rating') {
            $orderBy = array('Rating', 'DESC');
        } elseif ($sort == 'studentNum') {
            $orderBy = array('studentNum', 'DESC');
        } elseif ($sort == 'recommendedSeq') {
            $orderBy = array('recommendedSeq', 'ASC');
        } elseif ($sort == 'createdTimeByAsc') {
            $orderBy = array('createdTime', 'ASC');
        } else {
            $orderBy = array('createdTime', 'DESC');
        }

        return CourseSerialize::unserializes($this->getCourseDao()->searchCourses($conditions, $orderBy, $start, $limit));
    }

    public function searchCourseCount($conditions)
    {
        $conditions = $this->_prepareCourseConditions($conditions);
        return $this->getCourseDao()->searchCourseCount($conditions);
    }

    public function findRandomCourses($conditions, $num)
    {
        $count = $this->searchCourseCount($conditions);
        $max   = $count - $num - 1;
        if ($max < 0) {
            $max = 0;
        }
        $offset = rand(0, $max);
        return $this->searchCourses($conditions, 'default', $offset, $num);
    }

    protected function _prepareCourseConditions($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if ($value == 0) {
                return true;
            }

            return !empty($value);
        }

        );

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

    // TODO searchCoursesCount
    public function findUserLearnCourses($userId, $start, $limit, $onlyPublished = true)
    {
        $members = $this->getMemberDao()->findMembersByUserIdAndRole($userId, 'student', $start, $limit, $onlyPublished);

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

        foreach ($members as $member) {
            if (empty($courses[$member['courseId']])) {
                continue;
            }

            $courses[$member['courseId']]['memberIsLearned']  = $member['isLearned'];
            $courses[$member['courseId']]['memberLearnedNum'] = $member['learnedNum'];
        }

        return $courses;
    }

    // TODO searchCourse
    public function findUserLearnCoursesNotInClassroom($userId, $start, $limit, $onlyPublished = true)
    {
        $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndRole($userId, 'student', $start, $limit, $onlyPublished);

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

        return $courses;
    }

    // TODO 和findUserLearnCourseCountNotInClassroom合并
    public function findUserLearnCourseCount($userId, $onlyPublished = true)
    {
        return $this->getMemberDao()->findMemberCountByUserIdAndRole($userId, 'student', $onlyPublished);
    }

    public function findUserLearnCourseCountNotInClassroom($userId, $onlyPublished = true)
    {
        return $this->getMemberDao()->findMemberCountNotInClassroomByUserIdAndRole($userId, 'student', $onlyPublished);
    }

    public function findUserLeaningCourseCount($userId, $filters = array())
    {
        if (isset($filters["type"])) {
            return $this->getMemberDao()->findMemberCountByUserIdAndCourseTypeAndIsLearned($userId, 'student', $filters["type"], 0);
        }

        return $this->getMemberDao()->findMemberCountByUserIdAndRoleAndIsLearned($userId, 'student', 0);
    }

    public function findUserLeaningCourses($userId, $start, $limit, $filters = array())
    {
        if (isset($filters["type"])) {
            $members = $this->getMemberDao()->findMembersByUserIdAndCourseTypeAndIsLearned($userId, 'student', $filters["type"], '0', $start, $limit);
        } else {
            $members = $this->getMemberDao()->findMembersByUserIdAndRoleAndIsLearned($userId, 'student', '0', $start, $limit);
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

    public function becomeStudentByClassroomJoined($courseId, $userId)
    {
        $isCourseStudent = $this->isCourseStudent($courseId, $userId);
        $classroom       = $this->getClassroomService()->findClassroomByCourseId($courseId);

        if ($classroom['classroomId']) {
            $member = $this->getClassroomService()->getClassroomMember($classroom['classroomId'], $userId);

            if (!$isCourseStudent && !empty($member) && array_intersect($member['role'], array('student', 'teacher', 'headTeacher', 'assistant'))) {
                $member = $this->createMemberByClassroomJoined($courseId, $userId, $member["classroomId"]);
                return $member;
            }
        }

        return array();
    }

    public function addCourseLessonReplay($courseLessonReplay)
    {
        return $this->getCourseLessonReplayDao()->addCourseLessonReplay($courseLessonReplay);
    }

    public function deleteLessonReplayByLessonId($lessonId, $lessonType = 'live')
    {
        return $this->getCourseLessonReplayDao()->deleteLessonReplayByLessonId($lessonId, $lessonType);
    }

    public function findUserLeanedCourseCount($userId, $filters = array())
    {
        if (isset($filters["type"])) {
            return $this->getMemberDao()->findMemberCountByUserIdAndCourseTypeAndIsLearned($userId, 'student', $filters["type"], 1);
        }

        return $this->getMemberDao()->findMemberCountByUserIdAndRoleAndIsLearned($userId, 'student', 1);
    }

    public function findUserLeanedCourses($userId, $start, $limit, $filters = array())
    {
        if (isset($filters["type"])) {
            $members = $this->getMemberDao()->findMembersByUserIdAndTypeAndIsLearned($userId, 'student', $filters["type"], '1', $start, $limit);
        } else {
            $members = $this->getMemberDao()->findMembersByUserIdAndRoleAndIsLearned($userId, 'student', '1', $start, $limit);
        }

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

        $sortedCourses = array();

        foreach ($members as $member) {
            if (empty($courses[$member['courseId']])) {
                continue;
            }

            $course                     = $courses[$member['courseId']];
            $course['memberIsLearned']  = 1;
            $course['memberLearnedNum'] = $member['learnedNum'];
            $sortedCourses[]            = $course;
        }

        return $sortedCourses;
    }

    public function findUserTeachCourseCount($conditions, $onlyPublished = true)
    {
        $members = $this->getMemberDao()->findAllMemberByUserIdAndRole($conditions['userId'], 'teacher', $onlyPublished);
        unset($conditions['userId']);

        $courseIds               = ArrayToolkit::column($members, 'courseId');
        $conditions["courseIds"] = $courseIds;

        if (count($courseIds) == 0) {
            return 0;
        }

        if ($onlyPublished) {
            $conditions["status"] = 'published';
        }

        return $this->searchCourseCount($conditions);
    }

    public function findUserTeachCourses($conditions, $start, $limit, $onlyPublished = true)
    {
        $members = $this->getMemberDao()->findAllMemberByUserIdAndRole($conditions['userId'], 'teacher', $onlyPublished);
        unset($conditions['userId']);

        $courseIds               = ArrayToolkit::column($members, 'courseId');
        $conditions["courseIds"] = $courseIds;

        if (count($courseIds) == 0) {
            return array();
        }

        if ($onlyPublished) {
            $conditions["status"] = 'published';
        }

        $courses = $this->searchCourses($conditions, 'latest', $start, $limit);

        return $courses;
    }

    public function findUserFavoritedCourseCount($userId)
    {
        return $this->getFavoriteDao()->getFavoriteCourseCountByUserId($userId);
    }

    public function findUserFavoritedCourses($userId, $start, $limit)
    {
        $courseFavorites = $this->getFavoriteDao()->findCourseFavoritesByUserId($userId, $start, $limit);
        $favoriteCourses = $this->getCourseDao()->findCoursesByIds(ArrayToolkit::column($courseFavorites, 'courseId'));
        return CourseSerialize::unserializes($favoriteCourses);
    }

    public function findFavoritesCountByCourseId($courseId)
    {
        return $this->getFavoriteDao()->findFavoritesCountByCourseId($courseId);
    }

    public function createCourse($course)
    {
        if (!ArrayToolkit::requireds($course, array('title'))) {
            throw $this->createServiceException('缺少必要字段，创建课程失败！');
        }

        $course                = ArrayToolkit::parts($course, array('title', 'buyable', 'type', 'about', 'categoryId', 'tags', 'price', 'startTime', 'endTime', 'locationId', 'address', 'orgCode'));
        $course['status']      = 'draft';
        $course['about']       = !empty($course['about']) ? $this->purifyHtml($course['about']) : '';
        $course['tags']        = !empty($course['tags']) ? $course['tags'] : '';
        $course['userId']      = $this->getCurrentUser()->id;
        $course['createdTime'] = time();
        $course['teacherIds']  = array($course['userId']);
        $course                = $this->fillOrgId($course);
        $course                = $this->getCourseDao()->addCourse(CourseSerialize::serialize($course));

        $member = array(
            'courseId'    => $course['id'],
            'userId'      => $course['userId'],
            'role'        => 'teacher',
            'createdTime' => time()
        );

        $this->getMemberDao()->addMember($member);

        $course = $this->getCourse($course['id']);

        $this->dispatchEvent("course.create", $course);

        $this->getLogService()->info('course', 'create', "创建课程《{$course['title']}》(#{$course['id']})");

        return $course;
    }

    public function updateCourse($id, $fields)
    {
        $argument = $fields;
        $course   = $this->getCourseDao()->getCourse($id);

        if (empty($course)) {
            throw $this->createServiceException('课程不存在，更新失败！');
        }

        $fields = $this->_filterCourseFields($fields);

        $this->getLogService()->info('course', 'update', "更新课程《{$course['title']}》(#{$course['id']})的信息", $fields);

        $fields        = $this->fillOrgId($fields);
        $fields        = CourseSerialize::serialize($fields);
        $updatedCourse = $this->getCourseDao()->updateCourse($id, $fields);

        $this->dispatchEvent("course.update", array('argument' => $argument, 'course' => $updatedCourse, 'sourceCourse' => $course));

        return CourseSerialize::unserialize($updatedCourse);
    }

    public function batchUpdateOrg($courseIds, $orgCode)
    {
        if (!is_array($courseIds)) {
            $courseIds = array($courseIds);
        }

        $fields = $this->fillOrgId(array('orgCode' => $orgCode));

        foreach ($courseIds as $courseId) {
            $user = $this->getCourseDao()->updateCourse($courseId, $fields);
        }
    }

    // TODO refactor
    public function updateCourseCounter($id, $counter)
    {
        $fields = ArrayToolkit::parts($counter, array('rating', 'ratingNum', 'lessonNum', 'giveCredit'));

        if (empty($fields)) {
            throw $this->createServiceException('参数不正确，更新计数器失败！');
        }

        $this->getCourseDao()->updateCourse($id, $fields);
    }

    protected function _filterCourseFields($fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'title'          => '',
            'subtitle'       => '',
            'about'          => '',
            'expiryDay'      => 0,
            'serializeMode'  => 'none',
            'categoryId'     => 0,
            'vipLevelId'     => 0,
            'goals'          => array(),
            'audiences'      => array(),
            'tags'           => '',
            'startTime'      => 0,
            'endTime'        => 0,
            'locationId'     => 0,
            'address'        => '',
            'maxStudentNum'  => 0,
            'watchLimit'     => 0,
            'approval'       => 0,
            'maxRate'        => 0,
            'locked'         => 0,
            'tryLookable'    => 0,
            'tryLookTime'    => 0,
            'buyable'        => 0,
            'conversationId' => '',
            'orgCode'        => '1.',
            'orgId'          => 0
        ));

        if (!empty($fields['tags'])) {
            $fields['tags'] = explode(',', $fields['tags']);
            $fields['tags'] = $this->getTagService()->findTagsByNames($fields['tags']);
            array_walk($fields['tags'], function (&$item, $key) {
                $item = (int) $item['id'];
            }

            );
        }

        return $fields;
    }

    public function changeCoursePicture($courseId, $data)
    {
        $course = $this->getCourseDao()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createServiceException('课程不存在，图标更新失败！');
        }

        $fileIds = ArrayToolkit::column($data, "id");
        $files   = $this->getFileService()->getFilesByIds($fileIds);

        $files   = ArrayToolkit::index($files, "id");
        $fileIds = ArrayToolkit::index($data, "type");

        $fields = array(
            'smallPicture'  => $files[$fileIds["small"]["id"]]["uri"],
            'middlePicture' => $files[$fileIds["middle"]["id"]]["uri"],
            'largePicture'  => $files[$fileIds["large"]["id"]]["uri"]
        );

        $this->deleteNotUsedPictures($course);

        $this->getLogService()->info('course', 'update_picture', "更新课程《{$course['title']}》(#{$course['id']})图片", $fields);

        $update_picture = $this->getCourseDao()->updateCourse($courseId, $fields);

        $this->dispatchEvent("course.picture.update", array('argument' => $data, 'course' => $update_picture));

        return $update_picture;
    }

    protected function deleteNotUsedPictures($course)
    {
        $oldPictures = array(
            'smallPicture'  => $course['smallPicture'] ? $course['smallPicture'] : null,
            'middlePicture' => $course['middlePicture'] ? $course['middlePicture'] : null,
            'largePicture'  => $course['largePicture'] ? $course['largePicture'] : null
        );

        $courseCount = $this->searchCourseCount(array('smallPicture' => $course['smallPicture']));

        if ($courseCount <= 1) {
            $fileService = $this->getFileService();
            array_map(function ($oldPicture) use ($fileService) {
                if (!empty($oldPicture)) {
                    $fileService->deleteFileByUri($oldPicture);
                }
            }, $oldPictures);
        }
    }

    public function recommendCourse($id, $number)
    {
        $course = $this->tryAdminCourse($id);

        if (!is_numeric($number)) {
            throw $this->createAccessDeniedException('推荐课程序号只能为数字！');
        }

        $course = $this->getCourseDao()->updateCourse($id, array(
            'recommended'     => 1,
            'recommendedSeq'  => (int) $number,
            'recommendedTime' => time()
        ));

        $this->getLogService()->info('course', 'recommend', "推荐课程《{$course['title']}》(#{$course['id']}),序号为{$number}");

        return $course;
    }

    // TODO hitCourse 和 waveCourse合并
    public function hitCourse($id)
    {
        $checkCourse = $this->getCourse($id);

        if (empty($checkCourse)) {
            throw $this->createServiceException("课程不存在，操作失败。");
        }

        $this->getCourseDao()->waveCourse($id, 'hitNum', +1);
    }

    public function waveCourse($id, $field, $diff)
    {
        return $this->getCourseDao()->waveCourse($id, $field, $diff);
    }

    public function cancelRecommendCourse($id)
    {
        $course = $this->tryAdminCourse($id);

        $this->getCourseDao()->updateCourse($id, array(
            'recommended'     => 0,
            'recommendedTime' => 0,
            'recommendedSeq'  => 0
        ));

        $this->getLogService()->info('course', 'cancel_recommend', "取消推荐课程《{$course['title']}》(#{$course['id']})");
    }

    public function deleteCourse($id)
    {
        $course  = $this->tryAdminCourse($id);
        $lessons = $this->getCourseLessons($course['id']);

        // Delete course related data
        $this->getMemberDao()->deleteMembersByCourseId($id);
        $this->getLessonDao()->deleteLessonsByCourseId($id);
        $this->getLessonExtendDao()->deleteLessonsByCourseId($id);
        $this->deleteCrontabs($lessons);
        $this->getChapterDao()->deleteChaptersByCourseId($id);

        $this->getCourseDao()->deleteCourse($id);

        if ($course["type"] == "live") {
            $this->getCourseLessonReplayDao()->deleteLessonReplayByCourseId($id);
        }

        $this->getLogService()->info('course', 'delete', "删除课程《{$course['title']}》(#{$course['id']})");
        $this->dispatchEvent("course.delete", $course);

        return true;
    }

    public function publishCourse($id, $source = 'course')
    {
        if ($source == 'course') {
            $course = $this->tryManageCourse($id);
        } elseif ($source == 'classroom') {
            $course = $this->getCourseDao()->getCourse($id);

            if (empty($course)) {
                throw $this->createNotFoundException();
            }
        }

        $course = $this->getCourseDao()->updateCourse($id, array('status' => 'published'));
        $this->getLogService()->info('course', 'publish', "发布课程《{$course['title']}》(#{$course['id']})");
        $this->dispatchEvent('course.publish', $course);
    }

    public function closeCourse($id, $source = 'course')
    {
        if ($source == 'course') {
            $course = $this->tryManageCourse($id);
        } elseif ($source == 'classroom') {
            $course = $this->getCourseDao()->getCourse($id);

            if (empty($course)) {
                throw $this->createNotFoundException();
            }
        }

        $course = $this->getCourseDao()->updateCourse($id, array('status' => 'closed'));
        $this->getLogService()->info('course', 'close', "关闭课程《{$course['title']}》(#{$course['id']})");
        $this->dispatchEvent('course.close', $course);
    }

    public function favoriteCourse($courseId)
    {
        $user = $this->getCurrentUser();

        if (empty($user['id'])) {
            throw $this->createAccessDeniedException();
        }

        $course = $this->getCourse($courseId);

        if ($course['status'] != 'published') {
            throw $this->createServiceException('不能收藏未发布课程');
        }

        if (empty($course)) {
            throw $this->createServiceException("该课程不存在,收藏失败!");
        }

        $favorite = $this->getFavoriteDao()->getFavoriteByUserIdAndCourseId($user['id'], $course['id'], 'course');

        if ($favorite) {
            throw $this->createServiceException("该收藏已经存在，请不要重复收藏!");
        }

        //添加动态
        $this->dispatchEvent(
            'course.favorite',
            new ServiceEvent($course)
        );

        $this->getFavoriteDao()->addFavorite(array(
            'courseId'    => $course['id'],
            'userId'      => $user['id'],
            'createdTime' => time()
        ));

        return true;
    }

    public function unFavoriteCourse($courseId)
    {
        $user = $this->getCurrentUser();

        if (empty($user['id'])) {
            throw $this->createAccessDeniedException();
        }

        $course = $this->getCourse($courseId);

        if (empty($course)) {
            throw $this->createServiceException("该课程不存在,收藏失败!");
        }

        $favorite = $this->getFavoriteDao()->getFavoriteByUserIdAndCourseId($user['id'], $course['id'], 'course');

        if (empty($favorite)) {
            throw $this->createServiceException("你未收藏本课程，取消收藏失败!");
        }

        $this->getFavoriteDao()->deleteFavorite($favorite['id']);

        return true;
    }

    public function hasFavoritedCourse($courseId)
    {
        $user = $this->getCurrentUser();

        if (empty($user['id'])) {
            return false;
        }

        $course = $this->getCourse($courseId);

        if (empty($course)) {
            throw $this->createServiceException("课程{$courseId}不存在");
        }

        $favorite = $this->getFavoriteDao()->getFavoriteByUserIdAndCourseId($user['id'], $course['id'], 'course');

        return $favorite ? true : false;
    }

    public function searchCourseFavoriteCount($conditions)
    {
        return $this->getFavoriteDao()->searchCourseFavoriteCount($conditions);
    }

    public function searchCourseFavorites($conditions, $orderBy, $start, $limit)
    {
        return $this->getFavoriteDao()->searchCourseFavorites($conditions, $orderBy, $start, $limit);
    }

    public function analysisCourseDataByTime($startTime, $endTime)
    {
        return $this->getCourseDao()->analysisCourseDataByTime($startTime, $endTime);
    }

    public function findLearnedCoursesByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getMemberDao()->findLearnedCoursesByCourseIdAndUserId($courseId, $userId);
    }

    public function waveLearningTime($userId, $lessonId, $time)
    {
        $learn = $this->getLessonLearnDao()->getLearnByUserIdAndLessonId($userId, $lessonId);

        if ($time <= 200) {
            $this->getLessonLearnDao()->updateLearn($learn['id'], array(
                'learnTime' => $learn['learnTime'] + intval($time)
            ));
        }
    }

    public function waveWatchingTime($userId, $lessonId, $time)
    {
        $learn = $this->getLessonLearnDao()->getLearnByUserIdAndLessonId($userId, $lessonId);

        if ($time <= 200) {
            $learn = $this->getLessonLearnDao()->updateLearn($learn['id'], array(
                'watchTime'  => $learn['watchTime'] + intval($time),
                'updateTime' => time()
            ));
        }

        return $learn;
    }

    public function checkWatchNum($userId, $lessonId)
    {
        $lesson = $this->getLessonDao()->getLesson($lessonId);
        $course = $this->getCourse($lesson['courseId']);

        if (empty($course['watchLimit'])) {
            return array('status' => 'ignore');
        }

        $learn          = $this->getLessonLearnDao()->getLearnByUserIdAndLessonId($userId, $lessonId);
        $watchLimitTime = $lesson['length'] * $course['watchLimit'];

        if (empty($learn)) {
            return array('status' => 'ok', 'watchedTime' => 0, 'watchLimitTime' => $watchLimitTime);
        }

        if ($learn['watchTime'] < $watchLimitTime) {
            return array('status' => 'ok', 'watchedTime' => $learn['watchTime'], 'watchLimitTime' => $watchLimitTime);
        }

        return array('status' => 'error', 'watchedTime' => $learn['watchTime'], 'watchLimitTime' => $watchLimitTime);
    }

    public function setCoursePrice($courseId, $currency, $price)
    {
        if (!in_array($currency, array('coin', 'default'))) {
            throw $this->createServiceException("货币类型不正确");
        }

        $course = $this->getCourse($courseId);

        if (empty($course)) {
            throw $this->createServiceException("课程不存在");
        }

        $fields = array();

        if (($course['discountId'] > 0) && (intval($course['discount'] * 100) < 1000)) {
            $discount = $course['discount'];
        } else {
            $discount    = 10;
            $discountApp = $this->getAppService()->findInstallApp('Discount');

            if ($price > 0 && $discountApp) {
                $nowDiscount = $this->getDiscountService()->getNowGlobalDiscount();

                if ($nowDiscount) {
                    $fields['discountId'] = $nowDiscount['id'];
                    $fields['discount']   = $nowDiscount['globalDiscount'];
                    $discount             = $nowDiscount['globalDiscount'];
                }
            }
        }

        if ($currency == 'coin') {
            $fields['originPrice'] = $price;
            $fields['price']       = $price * ($discount / 10);
        } else {
            $fields['originPrice'] = $price;
            $fields['price']       = $price * ($discount / 10);
        }

        $course = $this->getCourseDao()->updateCourse($course['id'], $fields);
        $this->dispatchEvent("course.price.update", array('currency' => $currency, 'course' => $course));
        return $course;
    }

    public function setCoursesPriceWithDiscount($discountId)
    {
        $setting = $this->getSettingService()->get('coin');

        if (!empty($setting['coin_enabled']) && !empty($setting['price_type']) && strtolower($setting['price_type']) == 'coin') {
            $currency = 'coin';
        } else {
            $currency = 'default';
        }

        $discount = $this->getDiscountService()->getDiscount($discountId);

        if (empty($discount)) {
            throw $this->createServiceException("折扣活动#{#discountId}不存在！");
        }

        if ($discount['type'] == 'global') {
            $conditions = array('originPrice_GT' => '0.00');

            $count   = $this->searchCourseCount($conditions);
            $courses = $this->searchCourses($conditions, 'latest', 0, $count);

            foreach ($courses as $course) {
                $fields = array();

                if ($currency == 'coin') {
                    $fields = array(
                        'price' => $course['originPrice'] * $discount['globalDiscount'] / 10
                    );
                } else {
                    $fields = array(
                        'price' => $course['originPrice'] * $discount['globalDiscount'] / 10
                    );
                }

                $fields['discountId'] = $discount['id'];
                $fields['discount']   = $discount['globalDiscount'];

                $this->getCourseDao()->updateCourse($course['id'], $fields);
            }
        } else {
            $count     = $this->getDiscountService()->findItemsCountByDiscountId($discountId);
            $items     = $this->getDiscountService()->findItemsByDiscountId($discountId, 0, $count);
            $courseIds = ArrayToolkit::column($items, 'targetId');
            $courses   = $this->findCoursesByIds($courseIds);

            foreach ($items as $item) {
                if (empty($courses[$item['targetId']])) {
                    continue;
                }

                $course = $courses[$item['targetId']];

                if ($currency == 'coin') {
                    $fields = array(
                        'price' => $course['originPrice'] * $item['discount'] / 10
                    );
                } else {
                    $fields = array(
                        'price' => $course['originPrice'] * $item['discount'] / 10
                    );
                }

                $fields['discountId'] = $discount['id'];
                $fields['discount']   = $item['discount'];

                $this->getCourseDao()->updateCourse($course['id'], $fields);
            }
        }
    }

    public function revertCoursesPriceWithDiscount($discountId)
    {
        $discount = $this->getDiscountService()->getDiscount($discountId);

        if (empty($discount)) {
            throw $this->createServiceException("折扣活动#{#discountId}不存在！");
        }

        $this->getCourseDao()->clearCourseDiscountPrice($discountId);
    }

    /**
     * Lesslon API
     */

    public function getCourseLesson($courseId, $lessonId)
    {
        $lesson = $this->getLesson($lessonId);

        if (empty($lesson) || ($lesson['courseId'] != $courseId)) {
            return array();
        }

        return LessonSerialize::unserialize($lesson);
    }

    public function setCourseLessonMaxOnlineNum($lessonId, $num)
    {
        return $this->getLessonDao()->updateLesson($lessonId, array('maxOnlineNum' => $num));
    }

    public function findCourseDraft($courseId, $lessonId, $userId)
    {
        $draft = $this->getCourseDraftDao()->findCourseDraft($courseId, $lessonId, $userId);

        if (empty($draft) || ($draft['userId'] != $userId)) {
            return null;
        }

        return LessonSerialize::unserialize($draft);
    }

    public function getCourseLessons($courseId)
    {
        $lessons = $this->getLessonDao()->findLessonsByCourseId($courseId);
        return ArrayToolkit::index(LessonSerialize::unserializes($lessons), 'id');
    }

    public function deleteCourseDrafts($courseId, $lessonId, $userId)
    {
        return $this->getCourseDraftDao()->deleteCourseDrafts($courseId, $lessonId, $userId);
    }

    public function findLessonsByTypeAndMediaId($type, $mediaId)
    {
        $lessons = $this->getLessonDao()->findLessonsByTypeAndMediaId($type, $mediaId);
        return LessonSerialize::unserializes($lessons);
    }

    public function searchLessons($conditions, $orderBy, $start, $limit)
    {
        return $this->getLessonDao()->searchLessons($conditions, $orderBy, $start, $limit);
    }

    public function searchLessonCount($conditions)
    {
        return $this->getLessonDao()->searchLessonCount($conditions);
    }

    public function getCourseDraft($id)
    {
        return $this->getCourseDraftDao()->getCourseDraft($id);
    }

    public function createCourseDraft($draft)
    {
        $draft                = ArrayToolkit::parts($draft, array('userId', 'title', 'courseId', 'summary', 'content', 'lessonId', 'createdTime'));
        $draft['userId']      = $this->getCurrentUser()->id;
        $draft['createdTime'] = time();
        $draft                = $this->getCourseDraftDao()->addCourseDraft($draft);
        return $draft;
    }

    public function createLesson($lesson)
    {
        $argument = $lesson;
        $lesson   = ArrayToolkit::filter($lesson, array(
            'courseId'      => 0,
            'chapterId'     => 0,
            'free'          => 0,
            'title'         => '',
            'summary'       => '',
            'tags'          => array(),
            'type'          => 'text',
            'content'       => '',
            'media'         => array(),
            'mediaId'       => 0,
            'length'        => 0,
            'startTime'     => 0,
            'giveCredit'    => 0,
            'requireCredit' => 0,
            'liveProvider'  => 'none',
            'copyId'        => 0,
            'testMode'      => 'normal',
            'testStartTime' => 0,
            'suggestHours'  => '0.0'
        ));

        if (!ArrayToolkit::requireds($lesson, array('courseId', 'title', 'type'))) {
            throw $this->createServiceException('参数缺失，创建课时失败！');
        }

        if (empty($lesson['courseId'])) {
            throw $this->createServiceException('添加课时失败，课程ID为空。');
        }

        $course = $this->getCourse($lesson['courseId'], true);

        if (empty($course)) {
            throw $this->createServiceException('添加课时失败，课程不存在。');
        }

        if (!in_array($lesson['type'], array('text', 'audio', 'video', 'testpaper', 'live', 'ppt', 'document', 'flash', 'open', 'liveOpen'))) {
            throw $this->createServiceException('课时类型不正确，添加失败！');
        }

        $this->fillLessonMediaFields($lesson);

//课程内容的过滤 @todo

// if(isset($lesson['content'])){

//     $lesson['content'] = $this->purifyHtml($lesson['content']);

// }

        if (isset($fields['title'])) {
            $fields['title'] = $this->purifyHtml($fields['title']);
        }

        // 课程处于发布状态时，新增课时，课时默认的状态为“未发布"
        $lesson['status']      = $course['status'] == 'published' ? 'unpublished' : 'published';
        $lesson['free']        = empty($lesson['free']) ? 0 : 1;
        $lesson['number']      = $this->getNextLessonNumber($lesson['courseId']);
        $lesson['seq']         = $this->getNextCourseItemSeq($lesson['courseId']);
        $lesson['userId']      = $this->getCurrentUser()->id;
        $lesson['createdTime'] = time();

        $lastChapter         = $this->getChapterDao()->getLastChapterByCourseId($lesson['courseId']);
        $lesson['chapterId'] = empty($lastChapter) ? 0 : $lastChapter['id'];

        if ($lesson['type'] == 'live') {
            $lesson['endTime']      = $lesson['startTime'] + $lesson['length'] * 60;
            $lesson['suggestHours'] = $lesson['length'] / 60;
        }

        $lesson = $this->getLessonDao()->addLesson(
            LessonSerialize::serialize($lesson)
        );

        $argument['id'] = $lesson['id'];
        $lessonExtend   = $this->getLessonExtendDao()->addLesson($argument);
        $lesson         = array_merge($lesson, $lessonExtend);

        $this->updateCourseCounter($course['id'], array(
            'lessonNum'  => $this->getLessonDao()->getLessonCountByCourseId($course['id']),
            'giveCredit' => $this->getLessonDao()->sumLessonGiveCreditByCourseId($course['id'])
        ));

        $this->getLogService()->info('course', 'add_lesson', "添加课时《{$lesson['title']}》({$lesson['id']})", $lesson);
        $this->dispatchEvent("course.lesson.create", array('argument' => $argument, 'lesson' => $lesson));

        return $lesson;
    }

    public function analysisLessonDataByTime($startTime, $endTime)
    {
        return $this->getLessonDao()->analysisLessonDataByTime($startTime, $endTime);
    }

    public function findFutureLiveDates($courseIds, $limit)
    {
        return $this->getLessonDao()->findFutureLiveDates($courseIds, $limit);
    }

    public function findFutureLiveCourseIds()
    {
        return $this->getLessonDao()->findFutureLiveCourseIds();
    }

    public function findPastLiveCourseIds()
    {
        return $this->getLessonDao()->findPastLiveCourseIds();
    }

    protected function fillLessonMediaFields(&$lesson)
    {
        if (in_array($lesson['type'], array('video', 'audio', 'ppt', 'document', 'flash'))) {
            $media = empty($lesson['media']) ? null : $lesson['media'];

            if (empty($media) || empty($media['source']) || empty($media['name'])) {
                throw $this->createServiceException("media参数不正确，添加课时失败！");
            }

            if ($media['source'] == 'self') {
                $media['id'] = intval($media['id']);

                if (empty($media['id'])) {
                    throw $this->createServiceException("media id参数不正确，添加/编辑课时失败！");
                }

                $file = $this->getUploadFileService()->getFile($media['id']);

                if (empty($file)) {
                    throw $this->createServiceException('文件不存在，添加/编辑课时失败！');
                }

                $lesson['mediaId']     = $file['id'];
                $lesson['mediaName']   = $file['filename'];
                $lesson['mediaSource'] = 'self';
                $lesson['mediaUri']    = '';
            } else {
                if (empty($media['uri'])) {
                    throw $this->createServiceException("media uri参数不正确，添加/编辑课时失败！");
                }

                $lesson['mediaId']     = 0;
                $lesson['mediaName']   = $media['name'];
                $lesson['mediaSource'] = $media['source'];
                $lesson['mediaUri']    = $media['uri'];
            }
        } elseif ($lesson['type'] == 'testpaper' || $lesson['type'] == 'live') {
            unset($lesson['media']);
            return $lesson;
        } else {
            $lesson['mediaId']     = 0;
            $lesson['mediaName']   = '';
            $lesson['mediaSource'] = '';
            $lesson['mediaUri']    = '';
        }

        unset($lesson['media']);

        return $lesson;
    }

    public function updateCourseDraft($courseId, $lessonId, $userId, $fields)
    {
        $draft = $this->findCourseDraft($courseId, $lessonId, $userId);

        if (empty($draft)) {
            throw $this->createServiceException('草稿不存在，更新失败！');
        }

        $fields = $this->_filterDraftFields($fields);

        $this->getLogService()->info('course', 'update_draft', "更新草稿《{$draft['title']}》(#{$draft['id']})的信息", $fields);

        $fields = LessonSerialize::serialize($fields);

        return LessonSerialize::unserialize(
            $this->getCourseDraftDao()->updateCourseDraft($courseId, $lessonId, $userId, $fields)
        );
    }

    protected function _filterDraftFields($fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'title'       => '',
            'summary'     => '',
            'content'     => '',
            'createdTime' => 0
        ));
        return $fields;
    }

    public function updateLesson($courseId, $lessonId, $fields)
    {
        $argument = $fields;
        $course   = $this->getCourse($courseId);

        if (empty($course)) {
            throw $this->createServiceException("课程(#{$courseId})不存在！");
        }

        $lesson = $this->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            throw $this->createServiceException("课时(#{$lessonId})不存在！");
        }

        $fields = ArrayToolkit::filter($fields, array(
            'title'         => '',
            'summary'       => '',
            'content'       => '',
            'media'         => array(),
            'mediaId'       => 0,
            'number'        => 0,
            'seq'           => 0,
            'chapterId'     => 0,
            'free'          => 0,
            'length'        => 0,
            'startTime'     => 0,
            'giveCredit'    => 0,
            'requireCredit' => 0,
            'homeworkId'    => 0,
            'exerciseId'    => 0,
            'testMode'      => 'normal',
            'testStartTime' => 0,
            'suggestHours'  => '1.0',
            'replayStatus'  => 'ungenerated'
        ));

        if (isset($fields['title'])) {
            $fields['title'] = $this->purifyHtml($fields['title']);
        }

        $fields['type'] = $lesson['type'];

        if ($fields['type'] == 'live' && isset($fields['startTime'])) {
            $fields['endTime']      = $fields['startTime'] + $fields['length'] * 60;
            $fields['suggestHours'] = $fields['length'] / 60;
        }

        if (array_key_exists('media', $fields)) {
            $this->fillLessonMediaFields($fields);
        }

        $updatedLesson = LessonSerialize::unserialize(
            $this->getLessonDao()->updateLesson($lessonId, LessonSerialize::serialize($fields))
        );

        $lessonExtend = $this->updateLessonExtend($updatedLesson, $argument);
        $updateLesson = array_merge($updatedLesson, $lessonExtend);

        $this->updateCourseCounter($course['id'], array(
            'giveCredit' => $this->getLessonDao()->sumLessonGiveCreditByCourseId($course['id'])
        ));

        $this->getLogService()->info('course', 'update_lesson', "更新课时《{$updatedLesson['title']}》({$updatedLesson['id']})", $updatedLesson);

        $updatedLesson['fields'] = $lesson;
        $this->dispatchEvent("course.lesson.update", array('argument' => $argument, 'lesson' => $updatedLesson, 'sourceLesson' => $lesson));

        return $updatedLesson;
    }

    public function deleteLesson($courseId, $lessonId)
    {
        $course = $this->getCourse($courseId);

        if (empty($course)) {
            throw $this->createServiceException("课程(#{$courseId})不存在！");
        }

        $lesson = $this->getCourseLesson($courseId, $lessonId, true);

        if (empty($lesson)) {
            throw $this->createServiceException("课时(#{$lessonId})不存在！");
        }

        // 更新已学该课时学员的计数器
        $learnCount = $this->getLessonLearnDao()->findLearnsCountByLessonId($lessonId);

        if ($learnCount > 0) {
            $learns = $this->getLessonLearnDao()->findLearnsByLessonId($lessonId, 0, $learnCount);

            foreach ($learns as $learn) {
                if ($learn['status'] == 'finished') {
                    $member = $this->getCourseMember($learn['courseId'], $learn['userId']);

                    if ($member) {
                        $memberFields               = array();
                        $memberFields['learnedNum'] = $this->getLessonLearnDao()->getLearnCountByUserIdAndCourseIdAndStatus($learn['userId'], $learn['courseId'], 'finished') - 1;
                        $memberFields['isLearned']  = $memberFields['learnedNum'] >= $course['lessonNum'] ? 1 : 0;
                        $this->getMemberDao()->updateMember($member['id'], $memberFields);
                    }
                }
            }
        }

        $this->getLessonLearnDao()->deleteLearnsByLessonId($lessonId);

        $this->getLessonDao()->deleteLesson($lessonId);
        $this->getLessonExtendDao()->deleteLesson($lessonId);

        // 更新课时序号
        $this->updateCourseCounter($course['id'], array(
            'lessonNum' => $this->getLessonDao()->getLessonCountByCourseId($course['id'])
        ));

        $this->getLogService()->info('course', 'delete_lesson', "删除课程《{$course['title']}》(#{$course['id']})的课时 {$lesson['title']}");

        $this->dispatchEvent("course.lesson.delete", array(
            "courseId" => $courseId,
            "lesson"   => $lesson
        ));
    }

    public function sumLessonGiveCreditByLessonIds($lessonIds)
    {
        return $this->getLessonDao()->sumLessonGiveCreditByLessonIds($lessonIds);
    }

    public function findLearnsCountByLessonId($lessonId)
    {
        return $this->getLessonLearnDao()->findLearnsCountByLessonId($lessonId);
    }

    public function analysisLessonFinishedDataByTime($startTime, $endTime)
    {
        return $this->getLessonLearnDao()->analysisLessonFinishedDataByTime($startTime, $endTime);
    }

    public function searchAnalysisLessonViewCount($conditions)
    {
        return $this->getLessonViewDao()->searchLessonViewCount($conditions);
    }

    public function getAnalysisLessonMinTime($type)
    {
        if (!in_array($type, array('all', 'cloud', 'net', 'local'))) {
            throw $this->createServiceException("error");
        }

        return $this->getLessonViewDao()->getAnalysisLessonMinTime($type);
    }

    public function searchAnalysisLessonView($conditions, $orderBy, $start, $limit)
    {
        return $this->getLessonViewDao()->searchLessonView($conditions, $orderBy, $start, $limit);
    }

    public function analysisLessonViewDataByTime($startTime, $endTime, $conditions)
    {
        return $this->getLessonViewDao()->searchLessonViewGroupByTime($startTime, $endTime, $conditions);
    }

    public function publishLesson($courseId, $lessonId)
    {
        $course = $this->tryManageCourse($courseId);

        $lesson = $this->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            throw $this->createServiceException("课时#{$lessonId}不存在");
        }

        $publishLesson = $this->getLessonDao()->updateLesson($lesson['id'], array('status' => 'published'));

        $this->dispatchEvent("course.lesson.publish", $publishLesson);
    }

    public function unpublishLesson($courseId, $lessonId)
    {
        $course = $this->tryManageCourse($courseId);

        $lesson = $this->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            throw $this->createServiceException("课时#{$lessonId}不存在");
        }

        $unpublishLesson = $this->getLessonDao()->updateLesson($lesson['id'], array('status' => 'unpublished'));

        $this->dispatchEvent("course.lesson.unpublish", $unpublishLesson);
    }

    public function resetLessonMediaId($lessonId)
    {
        $lesson = $this->getLesson($lessonId);
        if ($lesson) {
            $this->getLessonDao()->updateLesson($lesson['id'], array('mediaId' => 0));
            return true;
        }

        return false;
    }

    public function getNextLessonNumber($courseId)
    {
        return $this->getLessonDao()->getLessonCountByCourseId($courseId) + 1;
    }

    public function liveLessonTimeCheck($courseId, $lessonId, $startTime, $length)
    {
        $course = $this->getCourseDao()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createServiceException('此课程不存在！');
        }

        $thisStartTime = $thisEndTime = 0;

        if ($lessonId) {
            $liveLesson    = $this->getCourseLesson($course['id'], $lessonId);
            $thisStartTime = empty($liveLesson['startTime']) ? 0 : $liveLesson['startTime'];
            $thisEndTime   = empty($liveLesson['endTime']) ? 0 : $liveLesson['endTime'];
        } else {
            $lessonId = "";
        }

        $startTime = is_numeric($startTime) ? $startTime : strtotime($startTime);
        $endTime   = $startTime + $length * 60;

        $thisLessons = $this->getLessonDao()->findTimeSlotOccupiedLessonsByCourseId($courseId, $startTime, $endTime, $lessonId);

        if (($length / 60) > 8) {
            return array('error_timeout', '时长不能超过8小时！');
        }

        if ($thisLessons) {
            return array('error_occupied', '该时段内已有直播课时存在，请调整直播开始时间');
        }

        return array('success', '');
    }

    public function calculateLiveCourseLeftCapacityInTimeRange($startTime, $endTime, $excludeLessonId)
    {
        $client              = new EdusohoLiveClient();
        $liveStudentCapacity = $client->getCapacity();
        $liveStudentCapacity = empty($liveStudentCapacity['capacity']) ? 0 : $liveStudentCapacity['capacity'];

        $lessons = $this->getLessonDao()->findTimeSlotOccupiedLessons($startTime, $endTime, $excludeLessonId);

        $courseIds               = ArrayToolkit::column($lessons, 'courseId');
        $courseIds               = array_unique($courseIds);
        $courseIds               = array_values($courseIds);
        $courses                 = $this->getCourseDao()->findCoursesByIds($courseIds);
        $maxStudentNum           = ArrayToolkit::column($courses, 'maxStudentNum');
        $timeSlotOccupiedStuNums = array_sum($maxStudentNum);

        return $liveStudentCapacity - $timeSlotOccupiedStuNums;
    }

    public function canLearnLesson($courseId, $lessonId)
    {
        list($course, $member) = $this->tryTakeCourse($courseId);
        $lesson                = $this->getCourseLesson($courseId, $lessonId);

        if (empty($lesson) || $lesson['courseId'] != $courseId) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();

        if (empty($lesson['requireCredit'])) {
            return array('status' => 'yes');
        }

        if ($member['credit'] >= $lesson['requireCredit']) {
            return array('status' => 'yes');
        }

        return array('status' => 'no', 'message' => sprintf('本课时需要%s学分才能学习，您当前学分为%s分。', $lesson['requireCredit'], $member['credit']));
    }

    public function startLearnLesson($courseId, $lessonId)
    {
        list($course, $member) = $this->tryTakeCourse($courseId);
        $user                  = $this->getCurrentUser();

        $lesson = $this->getCourseLesson($courseId, $lessonId);

        if (!empty($lesson)) {
            if ($lesson['type'] == 'video') {
                $createLessonView['courseId'] = $courseId;
                $createLessonView['lessonId'] = $lessonId;
                $createLessonView['fileId']   = $lesson['mediaId'];

                $file = array();

                if (!empty($createLessonView['fileId'])) {
                    $file = $this->getUploadFileService()->getFile($createLessonView['fileId']);
                }

                $createLessonView['fileStorage'] = empty($file) ? "net" : $file['storage'];
                $createLessonView['fileType']    = $lesson['type'];
                $createLessonView['fileSource']  = $lesson['mediaSource'];

                $this->createLessonView($createLessonView);
            }

            $learn = $this->getLessonLearnDao()->getLearnByUserIdAndLessonId($user['id'], $lessonId);

            if ($learn) {
                return false;
            }

            $learn = $this->getLessonLearnDao()->addLearn(array(
                'userId'       => $user['id'],
                'courseId'     => $courseId,
                'lessonId'     => $lessonId,
                'status'       => 'learning',
                'startTime'    => time(),
                'finishedTime' => 0
            ));

            $this->dispatchEvent(
                'course.lesson_start',
                new ServiceEvent($lesson, array('course' => $course, 'learn' => $learn))
            );

            return true;
        }

        return false;
    }

    public function createLessonView($createLessonView)
    {
        $createLessonView                = ArrayToolkit::parts($createLessonView, array('courseId', 'lessonId', 'fileId', 'fileType', 'fileStorage', 'fileSource'));
        $createLessonView['userId']      = $this->getCurrentUser()->id;
        $createLessonView['createdTime'] = time();

        $lessonView = $this->getLessonViewDao()->addLessonView($createLessonView);

        $lesson = $this->getCourseLesson($createLessonView['courseId'], $createLessonView['lessonId']);

        $this->getLogService()->info('course', 'create', "{$this->getCurrentUser()->nickname}观看课时《{$lesson['title']}》");

        return $lessonView;
    }

    public function finishLearnLesson($courseId, $lessonId)
    {
        list($course, $member) = $this->tryLearnCourse($courseId);

        $lesson = $this->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            throw $this->createServiceException("课时#{$lessonId}不存在！");
        }

        $learn = $this->getLessonLearnDao()->getLearnByUserIdAndLessonId($member['userId'], $lessonId);

        if ($learn) {
            $learn = $this->getLessonLearnDao()->updateLearn($learn['id'], array(
                'status'       => 'finished',
                'finishedTime' => time()
            ));
        } else {
            $learn = $this->getLessonLearnDao()->addLearn(array(
                'userId'       => $member['userId'],
                'courseId'     => $courseId,
                'lessonId'     => $lessonId,
                'status'       => 'finished',
                'startTime'    => time(),
                'finishedTime' => time()
            ));
        }

        $this->dispatchEvent(
            'course.lesson_finish',
            new ServiceEvent($lesson, array('course' => $course, 'learn' => $learn))
        );
    }

    public function searchLearnCount($conditions)
    {
        return $this->getLessonLearnDao()->searchLearnCount($conditions);
    }

    public function searchLearns($conditions, $orderBy, $start, $limit)
    {
        return $this->getLessonLearnDao()->searchLearns($conditions, $orderBy, $start, $limit);
    }

    public function searchLearnTime($conditions)
    {
        return $this->getLessonLearnDao()->searchLearnTime($conditions);
    }

    public function searchWatchTime($conditions)
    {
        return $this->getLessonLearnDao()->searchWatchTime($conditions);
    }

    public function findLatestFinishedLearns($start, $limit)
    {
        return $this->getLessonLearnDao()->findLatestFinishedLearns($start, $limit);
    }

    public function cancelLearnLesson($courseId, $lessonId)
    {
        list($course, $member) = $this->tryLearnCourse($courseId);

        $learn = $this->getLessonLearnDao()->getLearnByUserIdAndLessonId($member['userId'], $lessonId);

        if (empty($learn)) {
            throw $this->createServiceException("课时#{$lessonId}尚未学习，取消学习失败。");
        }

        if ($learn['status'] != 'finished') {
            throw $this->createServiceException("课时#{$lessonId}尚未学完，取消学习失败。");
        }

        $this->getLessonLearnDao()->updateLearn($learn['id'], array(
            'status'       => 'learning',
            'finishedTime' => 0
        ));

        $learns       = $this->getLessonLearnDao()->findLearnsByUserIdAndCourseIdAndStatus($member['userId'], $course['id'], 'finished');
        $totalCredits = $this->getLessonDao()->sumLessonGiveCreditByLessonIds(ArrayToolkit::column($learns, 'lessonId'));

        $memberFields               = array();
        $memberFields['learnedNum'] = count($learns);
        $memberFields['isLearned']  = $memberFields['learnedNum'] >= $course['lessonNum'] ? 1 : 0;
        $memberFields['credit']     = $totalCredits;

        $this->getMemberDao()->updateMember($member['id'], $memberFields);
    }

    public function getUserLearnLessonStatus($userId, $courseId, $lessonId)
    {
        $learn = $this->getLessonLearnDao()->getLearnByUserIdAndLessonId($userId, $lessonId);

        if (empty($learn)) {
            return null;
        }

        return $learn['status'];
    }

    public function getUserLearnLessonStatuses($userId, $courseId)
    {
        $learns = $this->getLessonLearnDao()->findLearnsByUserIdAndCourseId($userId, $courseId) ?: array();

        $statuses = array();

        foreach ($learns as $learn) {
            $statuses[$learn['lessonId']] = $learn['status'];
        }

        return $statuses;
    }

    public function getLearnByUserIdAndLessonId($userId, $lessonId)
    {
        return $this->getLessonLearnDao()->getLearnByUserIdAndLessonId($userId, $lessonId);
    }

    public function findUserLearnedLessons($userId, $courseId)
    {
        return ArrayToolkit::index($this->getLessonLearnDao()->findLearnsByUserIdAndCourseId($userId, $courseId) ?: array(), 'lessonId');
    }

    public function getUserNextLearnLesson($userId, $courseId)
    {
        $lessonIds = $this->getLessonDao()->findLessonIdsByCourseId($courseId);

        $learns = $this->getLessonLearnDao()->findLearnsByUserIdAndCourseIdAndStatus($userId, $courseId, 'finished');

        $learnedLessonIds = ArrayToolkit::column($learns, 'lessonId');

        $unlearnedLessonIds = array_diff($lessonIds, $learnedLessonIds);
        $nextLearnLessonId  = array_shift($unlearnedLessonIds);

        if (empty($nextLearnLessonId)) {
            return null;
        }

        return $this->getLessonDao()->getLesson($nextLearnLessonId);
    }

    public function getChapter($courseId, $chapterId)
    {
        $chapter = $this->getChapterDao()->getChapter($chapterId);

        if (empty($chapter) || $chapter['courseId'] != $courseId) {
            return null;
        }

        return $chapter;
    }

    public function getCourseChapters($courseId)
    {
        return $this->getChapterDao()->findChaptersByCourseId($courseId);
    }

    public function createChapter($chapter)
    {
        $argument = $chapter;

        if (!in_array($chapter['type'], array('chapter', 'unit'))) {
            throw $this->createServiceException("章节类型不正确，添加失败！");
        }

        if ($chapter['type'] == 'unit') {
            list($chapter['number'], $chapter['parentId']) = $this->getNextUnitNumberAndParentId($chapter['courseId']);
        } else {
            $chapter['number']   = $this->getNextChapterNumber($chapter['courseId']);
            $chapter['parentId'] = 0;
        }

        $chapter['seq']         = $this->getNextCourseItemSeq($chapter['courseId']);
        $chapter['createdTime'] = time();
        $chapter                = $this->getChapterDao()->addChapter($chapter);
        $this->dispatchEvent("chapter.create", array('argument' => $argument, 'chapter' => $chapter));
        return $chapter;
    }

    public function updateChapter($courseId, $chapterId, $fields)
    {
        $argument = $fields;
        $chapter  = $this->getChapter($courseId, $chapterId);

        if (empty($chapter)) {
            throw $this->createServiceException("章节#{$chapterId}不存在！");
        }

        $fields  = ArrayToolkit::parts($fields, array('title', 'number', 'seq', 'parentId'));
        $chapter = $this->getChapterDao()->updateChapter($chapterId, $fields);

        $this->dispatchEvent("chapter.update", array('argument' => $argument, 'chapter' => $chapter));
        return $chapter;
    }

    public function deleteChapter($courseId, $chapterId)
    {
        $course = $this->tryManageCourse($courseId);

        $deletedChapter = $this->getChapter($course['id'], $chapterId);

        if (empty($deletedChapter)) {
            throw $this->createServiceException(sprintf('章节(ID:%s)不存在，删除失败！', $chapterId));
        }

        $this->getChapterDao()->deleteChapter($deletedChapter['id']);

        $prevChapter = array('id' => 0);

        foreach ($this->getCourseChapters($course['id']) as $chapter) {
            if ($chapter['number'] < $deletedChapter['number']) {
                $prevChapter = $chapter;
            }
        }

        $lessons = $this->getLessonDao()->findLessonsByChapterId($deletedChapter['id']);

        foreach ($lessons as $lesson) {
            $this->getLessonDao()->updateLesson($lesson['id'], array('chapterId' => $prevChapter['id']));
        }

        $this->dispatchEvent("chapter.delete", $deletedChapter);
    }

    public function getNextChapterNumber($courseId)
    {
        $counter = $this->getChapterDao()->getChapterCountByCourseIdAndType($courseId, 'chapter');
        return $counter + 1;
    }

    public function findChaptersByCopyIdAndLockedCourseIds($copyId, $courseIds)
    {
        return $this->getChapterDao()->findChaptersByCopyIdAndLockedCourseIds($copyId, $courseIds);
    }

    public function getNextUnitNumberAndParentId($courseId)
    {
        $lastChapter = $this->getChapterDao()->getLastChapterByCourseIdAndType($courseId, 'chapter');

        $parentId = empty($lastChapter) ? 0 : $lastChapter['id'];

        $unitNum = 1 + $this->getChapterDao()->getChapterCountByCourseIdAndTypeAndParentId($courseId, 'unit', $parentId);

        return array($unitNum, $parentId);
    }

    public function getCourseItems($courseId)
    {
        $lessons = LessonSerialize::unserializes(
            $this->getLessonDao()->findLessonsByCourseId($courseId)
        );

        $chapters = $this->getChapterDao()->findChaptersByCourseId($courseId);

        $items = array();

        foreach ($lessons as $lesson) {
            $lesson['itemType']              = 'lesson';
            $items["lesson-{$lesson['id']}"] = $lesson;
        }

        foreach ($chapters as $chapter) {
            $chapter['itemType']               = 'chapter';
            $items["chapter-{$chapter['id']}"] = $chapter;
        }

        uasort($items, function ($item1, $item2) {
            return $item1['seq'] > $item2['seq'];
        }

        );
        return $items;
    }

    public function sortCourseItems($courseId, array $itemIds)
    {
        $items          = $this->getCourseItems($courseId);
        $existedItemIds = array_keys($items);

        if (count($itemIds) != count($existedItemIds)) {
            throw $this->createServiceException('itemdIds参数不正确');
        }

        $diffItemIds = array_diff($itemIds, array_keys($items));

        if (!empty($diffItemIds)) {
            throw $this->createServiceException('itemdIds参数不正确');
        }

        $lessonNum      = $chapterNum      = $unitNum      = $seq      = 0;
        $currentChapter = $rootChapter = array('id' => 0);

        foreach ($itemIds as $itemId) {
            $seq++;
            list($type) = explode('-', $itemId);

            switch ($type) {
                case 'lesson':
                    $lessonNum++;
                    $item   = $items[$itemId];
                    $fields = array('number' => $lessonNum, 'seq' => $seq, 'chapterId' => $currentChapter['id']);

                    if ($fields['number'] != $item['number'] || $fields['seq'] != $item['seq'] || $fields['chapterId'] != $item['chapterId']) {
                        $this->updateLesson($courseId, $item['id'], $fields);
                    }

                    break;
                case 'chapter':
                    $item    = $currentChapter    = $items[$itemId];
                    $chapter = $this->getChapter($courseId, $item['id']);

                    if ($item['type'] == 'unit') {
                        $unitNum++;
                        $fields = array('number' => $unitNum, 'seq' => $seq, 'parentId' => $rootChapter['id'], 'title' => $chapter['title']);
                    } else {
                        $chapterNum++;
                        $unitNum     = 0;
                        $rootChapter = $item;
                        $fields      = array('number' => $chapterNum, 'seq' => $seq, 'parentId' => 0, 'title' => $chapter['title']);
                    }

                    if ($fields['parentId'] != $item['parentId'] || $fields['number'] != $item['number'] || $fields['seq'] != $item['seq']) {
                        $argument = $fields;
                        $this->updateChapter($courseId, $item['id'], $fields);
                    }

                    break;
            }
        }
    }

    protected function getNextCourseItemSeq($courseId)
    {
        $chapterMaxSeq = $this->getChapterDao()->getChapterMaxSeqByCourseId($courseId);
        $lessonMaxSeq  = $this->getLessonDao()->getLessonMaxSeqByCourseId($courseId);
        return ($chapterMaxSeq > $lessonMaxSeq ? $chapterMaxSeq : $lessonMaxSeq) + 1;
    }

    public function addMemberExpiryDays($courseId, $userId, $day)
    {
        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if ($member['deadline'] > 0) {
            $deadline = $day * 24 * 60 * 60 + $member['deadline'];
        } else {
            $deadline = $day * 24 * 60 * 60 + time();
        }

        return $this->getMemberDao()->updateMember($member['id'], array(
            'deadline' => $deadline
        ));
    }

    /**
     * Member API
     */
    public function searchMemberCount($conditions)
    {
        $conditions = $this->_prepareCourseConditions($conditions);
        return $this->getMemberDao()->searchMemberCount($conditions);
    }

    public function countMembersByStartTimeAndEndTime($startTime, $endTime)
    {
        return $this->getMemberDao()->countMembersByStartTimeAndEndTime($startTime, $endTime);
    }

    public function findWillOverdueCourses()
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isLogin()) {
            throw $this->createServiceException('用户未登录');
        }

        $courseMembers = $this->getMemberDao()->findCourseMembersByUserId($currentUser["id"]);

        $courseIds = ArrayToolkit::column($courseMembers, "courseId");
        $courses   = $this->findCoursesByIds($courseIds);

        $courseMembers = ArrayToolkit::index($courseMembers, "courseId");

        $shouldNotifyCourses       = array();
        $shouldNotifyCourseMembers = array();

        $currentTime = time();

        foreach ($courses as $key => $course) {
            $courseMember = $courseMembers[$course["id"]];

            if ($course["expiryDay"] > 0 && $currentTime < $courseMember["deadline"] && (10 * 24 * 60 * 60 + $currentTime) > $courseMember["deadline"]) {
                $shouldNotifyCourses[]       = $course;
                $shouldNotifyCourseMembers[] = $courseMember;
            }
        }

        return array($shouldNotifyCourses, $shouldNotifyCourseMembers);
    }

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);
        return $this->getMemberDao()->searchMembers($conditions, $orderBy, $start, $limit);
    }

    public function searchMember($conditions, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);
        return $this->getMemberDao()->searchMember($conditions, $start, $limit);
    }

    public function searchMemberIds($conditions, $sort, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        if (is_array($sort)) {
            $orderBy = $sort;
        } else {
            $orderBy = array('createdTime', 'DESC');
        }

        return $this->getMemberDao()->searchMemberIds($conditions, $orderBy, $start, $limit);
    }

    public function findMemberUserIdsByCourseId($courseId)
    {
        return $this->getMemberDao()->findMemberUserIdsByCourseId($courseId);
    }

    public function updateCourseMember($id, $fields)
    {
        return $this->getMemberDao()->updateMember($id, $fields);
    }

    public function updateMembers($conditions, $updateFields)
    {
        return $this->getMemberDao()->updateMembers($conditions, $updateFields);
    }

    public function getCourseMember($courseId, $userId)
    {
        return $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);
    }

    public function findCourseStudents($courseId, $start, $limit)
    {
        return $this->getMemberDao()->findMembersByCourseIdAndRole($courseId, 'student', $start, $limit);
    }

    public function findCourseStudentsByCourseIds($courseIds)
    {
        return $this->getMemberDao()->getMembersByCourseIds($courseIds);
    }

    public function getCourseStudentCount($courseId)
    {
        return $this->getMemberDao()->findMemberCountByCourseIdAndRole($courseId, 'student');
    }

    public function findMobileVerifiedMemberCountByCourseId($courseId, $locked = 0)
    {
        return $this->getMemberDao()->findMobileVerifiedMemberCountByCourseId($courseId, $locked);
    }

    public function findCourseTeachers($courseId)
    {
        return $this->getMemberDao()->findMembersByCourseIdAndRole($courseId, 'teacher', 0, self::MAX_TEACHER);
    }

    public function isCourseTeacher($courseId, $userId)
    {
        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if (!$member) {
            return false;
        } else {
            return empty($member) || $member['role'] != 'teacher' ? false : true;
        }
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

    public function setCourseTeachers($courseId, $teachers)
    {
        // 过滤数据
        $teacherMembers = array();

        foreach (array_values($teachers) as $index => $teacher) {
            if (empty($teacher['id'])) {
                throw $this->createServiceException("教师ID不能为空，设置课程(#{$courseId})教师失败");
            }

            $user = $this->getUserService()->getUser($teacher['id']);

            if (empty($user)) {
                throw $this->createServiceException("用户不存在或没有教师角色，设置课程(#{$courseId})教师失败");
            }

            $teacherMembers[] = array(
                'courseId'    => $courseId,
                'userId'      => $user['id'],
                'role'        => 'teacher',
                'seq'         => $index,
                'isVisible'   => empty($teacher['isVisible']) ? 0 : 1,
                'createdTime' => time()
            );
        }

        // 先清除所有的已存在的教师学员
        $existTeacherMembers = $this->findCourseTeachers($courseId);

        foreach ($existTeacherMembers as $member) {
            $this->getMemberDao()->deleteMember($member['id']);
        }

        // 逐个插入新的教师的学员数据
        $visibleTeacherIds = array();

        foreach ($teacherMembers as $member) {
            // 存在学员信息，说明该用户先前是学生学员，则删除该学员信息。
            $existMember = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $member['userId']);

            if ($existMember) {
                $this->getMemberDao()->deleteMember($existMember['id']);
            }

            $member = $this->getMemberDao()->addMember($member);

            if ($member['isVisible']) {
                $visibleTeacherIds[] = $member['userId'];
            }
        }

        $this->getLogService()->info('course', 'update_teacher', "更新课程#{$courseId}的教师", $teacherMembers);

        // 更新课程的teacherIds，该字段为课程可见教师的ID列表
        $fields = array('teacherIds' => $visibleTeacherIds);
        $course = $this->getCourseDao()->updateCourse($courseId, CourseSerialize::serialize($fields));

        $this->dispatchEvent("course.teacher.update", array(
            "courseId" => $courseId,
            "course"   => $course,
            'teachers' => $teachers
        ));
    }

    /**
     * @todo 当用户拥有大量的课程老师角色时，这个方法效率是有就有问题咯！鉴于短期内用户不会拥有大量的课程老师角色，先这么做着。
     */
    public function cancelTeacherInAllCourses($userId)
    {
        $count   = $this->getMemberDao()->findMemberCountByUserIdAndRole($userId, 'teacher', false);
        $members = $this->getMemberDao()->findMembersByUserIdAndRole($userId, 'teacher', 0, $count, false);

        foreach ($members as $member) {
            $course = $this->getCourse($member['courseId']);

            $this->getMemberDao()->deleteMember($member['id']);

            $fields = array(
                'teacherIds' => array_diff($course['teacherIds'], array($member['userId']))
            );
            $this->getCourseDao()->updateCourse($member['courseId'], CourseSerialize::serialize($fields));
        }

        $this->getLogService()->info('course', 'cancel_teachers_all', "取消用户#{$userId}所有的课程老师角色");
    }

    public function remarkStudent($courseId, $userId, $remark)
    {
        $member = $this->getCourseMember($courseId, $userId);

        if (empty($member)) {
            throw $this->createServiceException('课程学员不存在，备注失败!');
        }

        $fields = array('remark' => empty($remark) ? '' : (string) $remark);
        return $this->getMemberDao()->updateMember($member['id'], $fields);
    }

    public function deleteMemberByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getMemberDao()->deleteMemberByCourseIdAndUserId($courseId, $userId);
    }

    public function deleteMemberByCourseIdAndRole($courseId, $role)
    {
        return $this->getMemberDao()->deleteMemberByCourseIdAndRole($courseId, $role);
    }

    public function deleteMemberByCourseId($courseId)
    {
        return $this->getMemberDao()->deleteMembersByCourseId($courseId);
    }

    public function becomeStudent($courseId, $userId, $info = array())
    {
        $course = $this->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException();
        }

        if (!in_array($course['status'], array('published'))) {
            throw $this->createServiceException('不能加入未发布课程');
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException("用户(#{$userId})不存在，加入课程失败！");
        }

        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if ($member) {
            throw $this->createServiceException("用户(#{$userId})已加入该课程！");
        }

        $levelChecked = '';

        if (!empty($info['becomeUseMember'])) {
            $levelChecked = $this->getVipService()->checkUserInMemberLevel($user['id'], $course['vipLevelId']);

            if ($levelChecked != 'ok') {
                throw $this->createServiceException("用户(#{$userId})不能以会员身份加入课程！");
            }

            $userMember = $this->getVipService()->getMemberByUserId($user['id']);
        }

        if ($course['expiryDay'] > 0) {
            $deadline = $course['expiryDay'] * 24 * 60 * 60 + time();
        } else {
            $deadline = 0;
        }

        if (!empty($info['orderId'])) {
            $order = $this->getOrderService()->getOrder($info['orderId']);

            if (empty($order)) {
                throw $this->createServiceException("订单(#{$info['orderId']})不存在，加入课程失败！");
            }
        } else {
            $order = null;
        }

        $conditions = array(
            'userId'   => $userId,
            'status'   => 'finished',
            'courseId' => $courseId
        );
        $count  = $this->getLessonLearnDao()->searchLearnCount($conditions);
        $fields = array(
            'courseId'    => $courseId,
            'userId'      => $userId,
            'orderId'     => empty($order) ? 0 : $order['id'],
            'deadline'    => $deadline,
            'levelId'     => empty($info['becomeUseMember']) ? 0 : $userMember['levelId'],
            'role'        => 'student',
            'remark'      => empty($order['note']) ? '' : $order['note'],
            'learnedNum'  => $count,
            'createdTime' => time()
        );

        if (empty($fields['remark'])) {
            $fields['remark'] = empty($info['note']) ? '' : $info['note'];
        }

        $member = $this->getMemberDao()->addMember($fields);

        $this->setMemberNoteNumber(
            $courseId,
            $userId,
            $this->getNoteDao()->getNoteCountByUserIdAndCourseId($userId, $courseId)
        );

        $setting = $this->getSettingService()->get('course', array());

        if (!empty($setting['welcome_message_enabled']) && !empty($course['teacherIds'])) {
            $message = $this->getWelcomeMessageBody($user, $course);
            $this->getMessageService()->sendMessage($course['teacherIds'][0], $user['id'], $message);
        }

        $fields = array(
            'studentNum' => $this->getCourseStudentCount($courseId)
        );

        if ($order) {
            $fields['income'] = $this->getOrderService()->sumOrderPriceByTarget('course', $courseId);
        }

        $this->getCourseDao()->updateCourse($courseId, $fields);
        $this->dispatchEvent(
            'course.join',
            new ServiceEvent($course, array('userId' => $member['userId'], 'member' => $member))
        );
        return $member;
    }

    public function createMemberByClassroomJoined($courseId, $userId, $classRoomId, array $info = array())
    {
        $fields = array(
            'courseId'    => $courseId,
            'userId'      => $userId,
            'orderId'     => empty($info["orderId"]) ? 0 : $info["orderId"],
            'deadline'    => empty($info['deadline']) ? 0 : $info['deadline'],
            'levelId'     => empty($info['levelId']) ? 0 : $info['levelId'],
            'role'        => 'student',
            'remark'      => empty($info["orderNote"]) ? '' : $info["orderNote"],
            'createdTime' => time(),
            'classroomId' => $classRoomId,
            'joinedType'  => 'classroom'
        );
        $isMember = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if ($isMember) {
            return array();
        }

        $member = $this->getMemberDao()->addMember($fields);
        $fields = array(
            'studentNum' => $this->getCourseStudentCount($courseId)
        );
        $this->getCourseDao()->updateCourse($courseId, $fields);
        return $member;
    }

    protected function getWelcomeMessageBody($user, $course)
    {
        $setting            = $this->getSettingService()->get('course', array());
        $valuesToBeReplace  = array('{{nickname}}', '{{course}}');
        $valuesToReplace    = array($user['nickname'], $course['title']);
        $welcomeMessageBody = str_replace($valuesToBeReplace, $valuesToReplace, $setting['welcome_message_body']);
        return $welcomeMessageBody;
    }

    public function removeStudent($courseId, $userId)
    {
        $course = $this->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException("课程(#${$courseId})不存在，退出课程失败。");
        }

        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if (empty($member) || ($member['role'] != 'student')) {
            throw $this->createServiceException("用户(#{$userId})不是课程(#{$courseId})的学员，退出课程失败。");
        }

        $this->getMemberDao()->deleteMember($member['id']);

        $this->getCourseDao()->updateCourse($courseId, array(
            'studentNum' => $this->getCourseStudentCount($courseId)
        ));

        $this->getLogService()->info('course', 'remove_student', "课程《{$course['title']}》(#{$course['id']})，移除学员#{$member['id']}");
        $this->dispatchEvent(
            'course.quit',
            new ServiceEvent($course, array('userId' => $member['userId'], 'member' => $member))
        );
    }

    public function lockStudent($courseId, $userId)
    {
        $course = $this->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException("课程(#${$courseId})不存在，封锁学员失败。");
        }

        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if (empty($member) || ($member['role'] != 'student')) {
            throw $this->createServiceException("用户(#{$userId})不是课程(#{$courseId})的学员，封锁学员失败。");
        }

        if ($member['locked']) {
            return;
        }

        $this->getMemberDao()->updateMember($member['id'], array('locked' => 1));
    }

    public function unlockStudent($courseId, $userId)
    {
        $course = $this->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException("课程(#${$courseId})不存在，封锁学员失败。");
        }

        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if (empty($member) || ($member['role'] != 'student')) {
            throw $this->createServiceException("用户(#{$userId})不是课程(#{$courseId})的学员，解封学员失败。");
        }

        if (empty($member['locked'])) {
            return;
        }

        $this->getMemberDao()->updateMember($member['id'], array('locked' => 0));
    }

    public function increaseLessonQuizCount($lessonId)
    {
        $lesson = $this->getLessonDao()->getLesson($lessonId);
        $lesson['quizNum'] += 1;
        $this->getLessonDao()->updateLesson($lesson['id'], $lesson);
    }

    public function resetLessonQuizCount($lessonId, $count)
    {
        $lesson            = $this->getLessonDao()->getLesson($lessonId);
        $lesson['quizNum'] = $count;
        $this->getLessonDao()->updateLesson($lesson['id'], $lesson);
    }

    public function increaseLessonMaterialCount($lessonId)
    {
        $lesson = $this->getLessonDao()->getLesson($lessonId);
        $lesson['materialNum'] += 1;
        $this->getLessonDao()->updateLesson($lesson['id'], $lesson);
    }

    public function resetLessonMaterialCount($lessonId, $count)
    {
        $lesson                = $this->getLessonDao()->getLesson($lessonId);
        $lesson['materialNum'] = $count;
        $this->getLessonDao()->updateLesson($lesson['id'], $lesson);
    }

    public function setMemberNoteNumber($courseId, $userId, $number)
    {
        $member = $this->getCourseMember($courseId, $userId);

        if (empty($member)) {
            return false;
        }

        $this->getMemberDao()->updateMember($member['id'], array(
            'noteNum'            => (int) $number,
            'noteLastUpdateTime' => time()
        ));

        return true;
    }

    /**
     * @todo refactor it.
     */
    public function tryManageCourse($courseId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('未登录用户，无权操作！');
        }

        $course = $this->getCourseDao()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException();
        }

        if (!$this->hasCourseManagerRole($courseId, $user['id'])) {
            throw $this->createAccessDeniedException('您不是课程的教师或管理员，无权操作！');
        }

        return CourseSerialize::unserialize($course);
    }

    public function tryAdminCourse($courseId)
    {
        $course = $this->getCourseDao()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();

        if (empty($user->id)) {
            throw $this->createAccessDeniedException('未登录用户，无权操作！');
        }

        if (count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) == 0) {
            throw $this->createAccessDeniedException('您不是管理员，无权操作！');
        }

        return CourseSerialize::unserialize($course);
    }

    public function canManageCourse($courseId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $course = $this->getCourse($courseId);

        if (empty($course)) {
            return $user->isAdmin();
        }

        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $user->id);

        if ($member && ($member['role'] == 'teacher')) {
            return true;
        }

        return false;
    }

    public function tryTakeCourse($courseId)
    {
        $course = $this->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('您尚未登录用户，请登录后再查看！');
        }

        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $user['id']);

        if (count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) > 0) {
            return array($course, $member);
        }

        if (empty($member) && $this->isClassroomMember($course, $user['id'])) {
            if (!$this->isCourseTeacher($course['id'], $user['id']) && !$this->isCourseStudent($course['id'], $user['id'])) {
                $member = $this->becomeStudentByClassroomJoined($course['id'], $user['id']);
                return array($course, $member);
            }
        }

        if (empty($member) || !in_array($member['role'], array('teacher', 'student'))) {
            throw $this->createAccessDeniedException('您不是课程学员，不能查看课程内容，请先购买课程！');
        }

        return array($course, $member);
    }

    public function isMemberNonExpired($course, $member)
    {
        if (empty($course) || empty($member)) {
            throw $this->createServiceException("course, member参数不能为空");
        }

        /*
        如果课程设置了限免时间，那么即使expiryDay为0，学员到了deadline也不能参加学习
        if ($course['expiryDay'] == 0) {
        return true;
        }
         */

        if ($member['deadline'] == 0) {
            return true;
        }

        if ($member['deadline'] > time()) {
            return true;
        }

        return false;
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

        if (count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) > 0) {
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

    public function tryLearnCourse($courseId)
    {
        $course = $this->getCourseDao()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();

        if (empty($user)) {
            throw $this->createAccessDeniedException('未登录用户，无权操作！');
        }

        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $user['id']);

        if (empty($member) || !in_array($member['role'], array('admin', 'teacher', 'student'))) {
            throw $this->createAccessDeniedException('您不是课程学员，不能学习！');
        }

        return array($course, $member);
    }

    public function generateLessonReplay($courseId, $lessonId)
    {
        $courseReplay = array('courseId' => $courseId, 'lessonId' => $lessonId);
        $course       = $this->tryManageCourse($courseId);
        $lesson       = $this->getLessonDao()->getLesson($lessonId);
        $mediaId      = $lesson["mediaId"];
        $client       = new EdusohoLiveClient();
        $replayList   = $client->createReplayList($mediaId, "查看回放", $lesson["liveProvider"]);

        if (array_key_exists("error", $replayList)) {
            return $replayList;
        }

        $this->getCourseLessonReplayDao()->deleteLessonReplayByLessonId($lessonId);

        if (array_key_exists("data", $replayList)) {
            $replayList = json_decode($replayList["data"], true);
        }

        foreach ($replayList as $key => $replay) {
            $fields                = array();
            $fields["courseId"]    = $courseId;
            $fields["lessonId"]    = $lessonId;
            $fields["title"]       = $replay["subject"];
            $fields["replayId"]    = $replay["id"];
            $fields["userId"]      = $this->getCurrentUser()->id;
            $fields["createdTime"] = time();
            $courseLessonReplay    = $this->getCourseLessonReplayDao()->addCourseLessonReplay($fields);
        }

        $fields = array(
            "replayStatus" => "generated"
        );

        $lesson = $this->updateLesson($courseId, $lessonId, $fields);

        $this->dispatchEvent("course.lesson.generate.replay", $courseReplay);

        return $replayList;
    }

    public function generateLessonVideoReplay($courseId, $lessonId, $fileId)
    {
        $lesson = $this->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            throw $this->createServiceException("课时(#{$lessonId})不存在！");
        }

        $file = $this->getUploadFileService()->getFile($fileId);
        if (!$file) {
            throw $this->createServiceException("文件不存在");
        }

        $lessonFields = array(
            'mediaId'      => $file['id'],
            'mediaName'    => $file['filename'],
            'mediaSource'  => 'self',
            'replayStatus' => 'videoGenerated'
        );

        $updatedLesson = LessonSerialize::unserialize(
            $this->getLessonDao()->updateLesson($lessonId, LessonSerialize::serialize($lessonFields))
        );

        $this->dispatchEvent("course.lesson.generate.video.replay", array('lesson' => $updatedLesson));

        return $lesson;
    }

    public function entryReplay($lessonId, $courseLessonReplayId)
    {
        $lesson                = $this->getLessonDao()->getLesson($lessonId);
        list($course, $member) = $this->tryTakeCourse($lesson['courseId']);

        $courseLessonReplay = $this->getCourseLessonReplayDao()->getCourseLessonReplay($courseLessonReplayId);
        $user               = $this->getCurrentUser();

        $args = array(
            'liveId'   => $lesson["mediaId"],
            'replayId' => $courseLessonReplay["replayId"],
            'provider' => $lesson["liveProvider"],
            'user'     => $user['email'],
            'nickname' => $user['nickname']
        );

        $client = new EdusohoLiveClient();
        $result = $client->entryReplay($args);
        return $result;
    }

    public function getCourseLessonReplayByLessonId($lessonId, $lessonType = 'live')
    {
        return $this->getCourseLessonReplayDao()->getCourseLessonReplayByLessonId($lessonId, $lessonType);
    }

    public function deleteCourseLessonReplayByLessonId($lessonId)
    {
        $this->getCourseLessonReplayDao()->deleteLessonReplayByLessonId($lessonId);
    }

    public function getCourseLessonReplayByCourseIdAndLessonId($courseId, $lessonId, $lessonType = 'live')
    {
        return $this->getCourseLessonReplayDao()->getCourseLessonReplayByCourseIdAndLessonId($courseId, $lessonId, $lessonType);
    }

    public function getCourseLessonReplay($id)
    {
        return $this->getCourseLessonReplayDao()->getCourseLessonReplay($id);
    }

    public function findCoursesByStudentIdAndCourseIds($studentId, $courseIds)
    {
        if (empty($courseIds) || count($courseIds) == 0) {
            return array();
        }

        $courseMembers = $this->getMemberDao()->findCoursesByStudentIdAndCourseIds($studentId, $courseIds);
        return $courseMembers;
    }

    public function updateCourseLessonReplay($id, $fields)
    {
        $replayCourse = $this->getCourseLessonReplayDao()->getCourseLessonReplay($id);

        if (empty($replayCourse)) {
            throw $this->createServiceException('录播回放不存在，更新失败！');
        }

        $fields = ArrayToolkit::parts($fields, array('hidden', 'title'));

        $updatedCourseLessonReplay = $this->getCourseLessonReplayDao()->updateCourseLessonReplay($id, $fields);

        return $updatedCourseLessonReplay;
    }

    public function updateCourseLessonReplayByLessonId($lessonId, $fields, $lessonType = 'live')
    {
        $fields = ArrayToolkit::parts($fields, array('hidden'));

        return $this->getCourseLessonReplayDao()->updateCourseLessonReplayByLessonId($lessonId, $fields, $lessonType);
    }

    public function searchCourseLessonReplayCount($conditions)
    {
        return $this->getCourseLessonReplayDao()->searchCourseLessonReplayCount($conditions);
    }

    public function searchCourseLessonReplays($conditions, $orderBy, $start, $limit)
    {
        return $this->getCourseLessonReplayDao()->searchCourseLessonReplays($conditions, $orderBy, $start, $limit);
    }

    protected function isClassroomMember($course, $userId)
    {
        $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);

        if ($classroom['classroomId']) {
            $member = $this->getClassroomService()->getClassroomMember($classroom['classroomId'], $userId);

            if (!empty($member) && array_intersect(array('student', 'teacher', 'headTeacher', 'assistant'), $member['role'])) {
                return true;
            }
        }

        return false;
    }

    protected function getCourseLessonReplayDao()
    {
        return $this->createDao('Course.CourseLessonReplayDao');
    }

    protected function hasAdminRole($courseId, $userId)
    {
        return $this->getUserService()->hasAdminRoles($userId);
    }

    public function hasTeacherRole($courseId, $userId)
    {
        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);
        return !empty($member) && $member['role'] == 'teacher';
    }

    protected function hasCourseManagerRole($courseId, $userId)
    {
        return $this->hasAdminRole($courseId, $userId) || $this->hasTeacherRole($courseId, $userId);
    }

    protected function deleteCrontabs($lessons)
    {
        if (!$lessons) {
            return false;
        }

        foreach ($lessons as $key => $lesson) {
            $this->getCrontabService()->deleteJobs($lesson['id'], 'lesson');
        }

        return true;
    }

    protected function updateLessonExtend($lesson, $fields)
    {
        $lessonExtend = $this->getLessonExtendDao()->getLesson($lesson['id']);
        if ($lessonExtend) {
            return $this->getLessonExtendDao()->updateLesson($lesson['id'], $fields);
        } else {
            $fields['id']       = $lesson['id'];
            $fields['courseId'] = $lesson['courseId'];
            return $this->getLessonExtendDao()->addLesson($fields);
        }
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course.CourseDao');
    }

    protected function getFavoriteDao()
    {
        return $this->createDao('Course.FavoriteDao');
    }

    protected function getMemberDao()
    {
        return $this->createDao('Course.CourseMemberDao');
    }

    protected function getLessonDao()
    {
        return $this->createDao('Course.LessonDao');
    }

    protected function getLessonExtendDao()
    {
        return $this->createDao('Course.LessonExtendDao');
    }

    protected function getCourseDraftDao()
    {
        return $this->createDao('Course.CourseDraftDao');
    }

    protected function getLessonLearnDao()
    {
        return $this->createDao('Course.LessonLearnDao');
    }

    protected function getLessonViewDao()
    {
        return $this->createDao('Course.LessonViewDao');
    }

    protected function getChapterDao()
    {
        return $this->createDao('Course.CourseChapterDao');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy.CategoryService');
    }

    protected function getFileService()
    {
        return $this->createService('Content.FileService');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order.OrderService');
    }

    protected function getVipService()
    {
        return $this->createService('Vip:Vip.VipService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }

    protected function getMessageService()
    {
        return $this->createService('User.MessageService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy.TagService');
    }

    protected function getNoteDao()
    {
        return $this->createDao('Course.CourseNoteDao');
    }

    protected function getCourseMaterialService()
    {
        return $this->createService('Course.MaterialService');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform.AppService');
    }

    protected function getDiscountService()
    {
        return $this->createService('Discount:Discount.DiscountService');
    }

    protected function getCrontabService()
    {
        return $this->createService('Crontab.CrontabService');
    }
}

class CourseSerialize
{
    public static function serialize(array &$course)
    {
        if (isset($course['tags'])) {
            if (is_array($course['tags']) && !empty($course['tags'])) {
                $course['tags'] = '|'.implode('|', $course['tags']).'|';
            } else {
                $course['tags'] = '';
            }
        }

        if (isset($course['goals'])) {
            if (is_array($course['goals']) && !empty($course['goals'])) {
                $course['goals'] = '|'.implode('|', $course['goals']).'|';
            } else {
                $course['goals'] = '';
            }
        }

        if (isset($course['audiences'])) {
            if (is_array($course['audiences']) && !empty($course['audiences'])) {
                $course['audiences'] = '|'.implode('|', $course['audiences']).'|';
            } else {
                $course['audiences'] = '';
            }
        }

        if (isset($course['teacherIds'])) {
            if (is_array($course['teacherIds']) && !empty($course['teacherIds'])) {
                $course['teacherIds'] = '|'.implode('|', $course['teacherIds']).'|';
            } else {
                $course['teacherIds'] = null;
            }
        }

        return $course;
    }

    public static function unserialize(array $course = null)
    {
        if (empty($course)) {
            return $course;
        }

        $course['tags'] = empty($course['tags']) ? array() : explode('|', trim($course['tags'], '|'));

        if (empty($course['goals'])) {
            $course['goals'] = array();
        } else {
            $course['goals'] = explode('|', trim($course['goals'], '|'));
        }

        if (empty($course['audiences'])) {
            $course['audiences'] = array();
        } else {
            $course['audiences'] = explode('|', trim($course['audiences'], '|'));
        }

        if (empty($course['teacherIds'])) {
            $course['teacherIds'] = array();
        } else {
            $course['teacherIds'] = explode('|', trim($course['teacherIds'], '|'));
        }

        return $course;
    }

    public static function unserializes(array $courses)
    {
        return array_map(function ($course) {
            return CourseSerialize::unserialize($course);
        }, $courses);
    }
}

class LessonSerialize
{
    public static function serialize(array $lesson)
    {
        return $lesson;
    }

    public static function unserialize(array $lesson = null)
    {
        return $lesson;
    }

    public static function unserializes(array $lessons)
    {
        return array_map(function ($lesson) {
            return LessonSerialize::unserialize($lesson);
        }, $lessons);
    }
}
