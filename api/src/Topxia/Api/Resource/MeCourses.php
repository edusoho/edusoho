<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Course\Impl\CourseServiceImpl;

class MeCourses extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = $request->query->all();
        $start      = $request->query->get('start', 0);
        $limit      = $request->query->get('limit', 10);
        $type       = $request->query->get('type', '');
        $relation   = $request->query->get('relation', '');
        $user       = getCurrentUser();

        if ($relation == 'learning') {
            $total   = $this->getCourseService()->findUserLearningCourseCountNotInClassroom($user['id'], $conditions);
            $courses = $this->getCourseService()->findUserLearningCoursesNotInClassroom(
                $user['id'],
                $start,
                $limit,
                empty($type) ? array() : array('type' => $type)
            );
        } elseif ($relation == 'learned') {
            $total   = $this->getCourseService()->findUserLeanedCourseCount($user['id'], $conditions);
            $courses = $this->getCourseService()->findUserLearnedCoursesNotInClassroom(
                $user['id'],
                $start,
                $limit,
                empty($type) ? array() : array('type' => $type)
            );
        } elseif ($relation == 'learn') {
            $total              = $this->getCourseService()->findUserLearnCourseCountNotInClassroom($user['id'], true);
            if (empty($type)) {
                $coursesAfterColumn = $this->getCourseService()->findUserLearnCoursesNotInClassroom(
                    $user['id'],
                    $start,
                    $limit
                );
            } else {
                $coursesAfterColumn = $this->getCourseService()->findUserLearnCoursesNotInClassroomWithType(
                    $user['id'],
                    $type,
                    $start,
                    $limit
                );
            }

            $courses = array_values($coursesAfterColumn);
        } elseif ($relation == 'teaching') {
            $total   = $this->getCourseService()->findUserTeachCourseCountNotInClassroom(array('userId' => $user['id']), false);
            $courses = $this->getCourseService()->findUserTeachCoursesNotInClassroom(
                array('userId' => $user['id']),
                $start,
                $limit,
                false
            );
        } elseif ($relation == 'favorited') {
            $total   = $this->getCourseService()->findUserFavoritedCourseCountNotInClassroom($user['id']);
            $courses = $this->getCourseService()->findUserFavoritedCoursesNotInClassroom(
                $user['id'],
                $start,
                $limit
            );
        } else {
            return $this->error('error', '缺少参数!');
        }

        $courses = $this->filter($courses);
        return $this->wrap($courses, count($courses));
    }

    public function filter($res)
    {
        return $this->multicallFilter('Course', $res);
    }

    protected function multicallFilter($name, $res)
    {
        $courses = array();
        foreach ($res as $key => $one) {
            $course           = $this->callFilter($name, $one);
            $courseConv       = $this->getConversationService()->getConversationByTarget($course['id'], 'course');
            $course['convNo'] = $courseConv ? $courseConv['no'] : '';
            if ($course['parentId'] > 0) {
                continue;
            }

            $courses[] = $course;
        }

        return $courses;
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }

    /**
     * @return CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
