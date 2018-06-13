<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Biz\Course\Service\Impl\CourseServiceImpl;
use Biz\Course\Service\Impl\CourseSetServiceImpl;

class MeCourses extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = $request->query->all();
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);
        $type = $request->query->get('type', '');
        $relation = $request->query->get('relation', '');
        $user = getCurrentUser();
        if ('learning' == $relation) {
            $total = $this->getCourseService()->findUserLearningCourseCountNotInClassroom($user['id'], $conditions);
            $courses = $this->getCourseService()->findUserLearningCoursesNotInClassroom(
                $user['id'],
                $start,
                $limit,
                empty($type) ? array() : array('type' => $type)
            );
        } elseif ('learned' == $relation) {
            $total = $this->getCourseService()->findUserLeanedCourseCount($user['id'], $conditions);
            $courses = $this->getCourseService()->findUserLearnedCoursesNotInClassroom(
                $user['id'],
                $start,
                $limit,
                empty($type) ? array() : array('type' => $type)
            );
        } elseif ('learn' == $relation) {
            $total = $this->getCourseService()->findUserLearnCourseCountNotInClassroom($user['id'], true, true);
            if (empty($type)) {
                $coursesAfterColumn = $this->getCourseService()->findUserLearnCoursesNotInClassroom(
                    $user['id'],
                    $start,
                    $limit,
                    true,
                    true
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
        } elseif ('teaching' == $relation) {
            $total = $this->getCourseService()->findUserTeachCourseCountNotInClassroom(array('userId' => $user['id']), false);
            $courses = $this->getCourseService()->findUserTeachCoursesNotInClassroom(
                array(
                    'userId' => $user['id'],
                    'excludeTypes' => array('reservation'),
                ),
                $start,
                $limit,
                false
            );
        } elseif ('favorited' == $relation) {
            $total = $this->getCourseService()->findUserFavoritedCourseCountNotInClassroom($user['id']);
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
        $courseIds = ArrayToolkit::column($res, 'id');

        $courseSets = $this->getCourseSetService()->findCourseSetsByCourseIds($courseIds);
        foreach ($res as $key => $one) {
            $one['courseSet'] = $courseSets[$one['courseSetId']];
            $course = $this->callFilter($name, $one);
            $courseConv = $this->getConversationService()->getConversationByTarget($course['id'], 'course');
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
        return $this->createService('IM:ConversationService');
    }

    /**
     * @return CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetServiceImpl
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
