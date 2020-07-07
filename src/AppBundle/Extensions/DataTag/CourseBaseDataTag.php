<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Review\Service\ReviewService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;

abstract class CourseBaseDataTag extends BaseDataTag implements DataTag
{
    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getServiceKernel()->getBiz()->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getServiceKernel()->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->getBiz()->service('Course:MemberService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getServiceKernel()->getBiz()->service('User:UserService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getServiceKernel()->getBiz()->service('Task:TaskService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->getBiz()->service('Course:ThreadService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->getServiceKernel()->getBiz()->service('Review:ReviewService');
    }

    protected function getActivityService()
    {
        return $this->getServiceKernel()->createService('Activity:ActivityService');
    }

    protected function checkUserId(array $arguments)
    {
        if (empty($arguments['userId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('userId参数缺失'));
        }
    }

    protected function checkCategoryId(array $arguments)
    {
        if (empty($arguments['categoryId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('categoryId参数缺失'));
        }
    }

    protected function checkCount(array $arguments)
    {
        if (empty($arguments['count'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('count参数缺失'));
        }

        if ($arguments['count'] > 100) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('count参数超出最大取值范围'));
        }
    }

    protected function checkCourseId(array $arguments)
    {
        if (empty($arguments['courseId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('courseId参数缺失'));
        }
    }

    protected function checkCourseArguments(array $arguments)
    {
        if (empty($arguments['courseId'])) {
            $conditions = [];
        } else {
            $conditions = ['courseId' => $arguments['courseId']];
        }

        return $conditions;
    }

    protected function checkThreadId(array $arguments)
    {
        if (empty($arguments['threadId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('threadId参数缺失'));
        }
    }

    protected function checkReviewId(array $arguments)
    {
        if (empty($arguments['reviewId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('reviewId参数缺失'));
        }
    }

    protected function checkGroupId(array $arguments)
    {
        if (empty($arguments['group'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('group参数缺失'));
        }
    }

    protected function checkLessonId(array $arguments)
    {
        if (empty($arguments['lessonId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('lessonId参数缺失'));
        }
    }

    protected function fillCourseSetTeachersAndCategoriesAttribute(array $courseSets)
    {
        $userIds = [];
        $categoryIds = [];

        foreach ($courseSets as &$set) {
            if (!empty($set['teacherIds'])) {
                $userIds = array_merge($userIds, $set['teacherIds']);
            }
            $categoryIds[] = $set['categoryId'];
        }

        $users = $this->getUserService()->findUsersByIds($userIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

        foreach ($users as $key => $user) {
            if ($profiles[$user['id']]['id'] == $user['id']) {
                $users[$key]['profile'] = $profiles[$user['id']];
            }
        }

        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        foreach ($courseSets as &$set) {
            $categoryId = $set['categoryId'];
            if (0 != $categoryId && array_key_exists($categoryId, $categories)) {
                $set['category'] = $categories[$categoryId];
            }

            $teachers = [];

            if (empty($set['teacherIds'])) {
                continue;
            }
            foreach ($set['teacherIds'] as $teacherId) {
                if (!$teacherId) {
                    continue;
                }

                $user = $users[$teacherId];
                unset($user['password']);
                unset($user['salt']);
                $teachers[] = $user;
            }

            $set['teachers'] = $teachers;
            unset($set['teacherIds']);
        }

        $courseSets = $this->fillCourseTryLookVideo($courseSets);

        return $courseSets;
    }

    protected function getCourseTeachersAndCategories($courses)
    {
        $userIds = [];
        $categoryIds = [];

        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
            //$categoryIds[] = $course['categoryId'];
        }

        $users = $this->getUserService()->findUsersByIds($userIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

        foreach ($users as $key => $user) {
            if ($profiles[$user['id']]['id'] == $user['id']) {
                $users[$key]['profile'] = $profiles[$user['id']];
            }
        }

        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        foreach ($courses as &$course) {
            $teachers = [];

            foreach ($course['teacherIds'] as $teacherId) {
                if (!$teacherId) {
                    continue;
                }

                $user = $users[$teacherId];
                unset($user['password']);
                unset($user['salt']);
                $teachers[] = $user;
            }

            $course['teachers'] = $teachers;

            $categoryId = $course['categoryId'];

            if (0 != $categoryId && array_key_exists($categoryId, $categories)) {
                $course['category'] = $categories[$categoryId];
            }
        }

        return $courses;
    }

    protected function getCoursesAndUsers($courseRelations)
    {
        $userIds = [];
        $courseIds = [];

        foreach ($courseRelations as &$courseRelation) {
            $userIds[] = $courseRelation['userId'];
            $courseIds[] = isset($courseRelation['courseId']) ? $courseRelation['courseId'] : $courseRelation['targetId'];
        }

        $users = $this->getUserService()->findUsersByIds($userIds);
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        foreach ($courseRelations as &$courseRelation) {
            $userId = $courseRelation['userId'];
            $user = $users[$userId];
            unset($user['password']);
            unset($user['salt']);
            $courseRelation['User'] = $user;

            $courseId = isset($courseRelation['courseId']) ? $courseRelation['courseId'] : $courseRelation['targetId'];
            $course = $courses[$courseId];
            $courseRelation['course'] = $course;
        }

        return $courseRelations;
    }

    protected function unsetUserPasswords($users)
    {
        foreach ($users as &$user) {
            unset($user['password']);
            unset($user['salt']);
        }

        return $users;
    }

    protected function fillCourseTryLookVideo($courseSets)
    {
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(ArrayToolkit::column($courseSets, 'id'));
        if (!empty($courses)) {
            $tryLookAbleCourses = array_filter($courses, function ($course) {
                return !empty($course['tryLookable']) && 'published' === $course['status'];
            });
            $tryLookAbleCourseIds = ArrayToolkit::column($tryLookAbleCourses, 'id');
            $activities = $this->getActivityService()->findActivitySupportVideoTryLook($tryLookAbleCourseIds);
            $activityIds = ArrayToolkit::column($activities, 'id');
            $tasks = $this->getTaskService()->findTasksByActivityIds($activityIds);
            $tasks = ArrayToolkit::index($tasks, 'activityId');

            $activities = array_filter($activities, function ($activity) use ($tasks) {
                return 'published' === $tasks[$activity['id']]['status'];
            });

            //返回有云视频任务的课程
            $activities = ArrayToolkit::index($activities, 'fromCourseId');
            foreach ($courses as &$course) {
                if (!empty($activities[$course['id']])) {
                    $course['tryLookVideo'] = 1;
                }
            }
            unset($course);
        }

        $tryLookVideoCourses = array_filter($courses, function ($course) {
            return !empty($course['tryLookVideo']);
        });
        $courses = ArrayToolkit::index($courses, 'courseSetId');
        $tryLookVideoCourses = ArrayToolkit::index($tryLookVideoCourses, 'courseSetId');

        array_walk($courseSets, function (&$courseSet) use ($courses, $tryLookVideoCourses) {
            if (isset($tryLookVideoCourses[$courseSet['id']])) {
                $courseSet['course'] = $tryLookVideoCourses[$courseSet['id']];
            } else {
                $courseSet['course'] = $courses[$courseSet['id']];
            }
        });

        return $courseSets;
    }
}
