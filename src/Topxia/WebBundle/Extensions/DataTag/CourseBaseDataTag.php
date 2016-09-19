<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

abstract class CourseBaseDataTag extends BaseDataTag implements DataTag
{
    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
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
            $conditions = array();
        } else {
            $conditions = array('courseId' => $arguments['courseId']);
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

    protected function getCourseTeachersAndCategories($courses)
    {
        $userIds     = array();
        $categoryIds = array();

        foreach ($courses as $course) {
            $userIds       = array_merge($userIds, $course['teacherIds']);
            $categoryIds[] = $course['categoryId'];
        }

        $users    = $this->getUserService()->findUsersByIds($userIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

        foreach ($users as $key => $user) {
            if ($user['id'] == $profiles[$user['id']]['id']) {
                $users[$key]['profile'] = $profiles[$user['id']];
            }
        }

        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        foreach ($courses as &$course) {
            $teachers = array();

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

            if ($categoryId != 0 && array_key_exists($categoryId, $categories)) {
                $course['category'] = $categories[$categoryId];
            }
        }

        return $courses;
    }

    protected function getCoursesAndUsers($courseRelations)
    {
        $userIds   = array();
        $courseIds = array();

        foreach ($courseRelations as &$courseRelation) {
            $userIds[]   = $courseRelation['userId'];
            $courseIds[] = $courseRelation['courseId'];
        }

        $users   = $this->getUserService()->findUsersByIds($userIds);
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        foreach ($courseRelations as &$courseRelation) {
            $userId = $courseRelation['userId'];
            $user   = $users[$userId];
            unset($user['password']);
            unset($user['salt']);
            $courseRelation['User'] = $user;

            $courseId                 = $courseRelation['courseId'];
            $course                   = $courses[$courseId];
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
}
