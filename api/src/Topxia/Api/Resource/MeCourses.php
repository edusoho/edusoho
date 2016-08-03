<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

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
            $total   = $this->getCourseService()->findUserLeaningCourseCount($user['id'], $conditions);
            $courses = $this->getCourseService()->findUserLeaningCourses(
                $user['id'],
                $start,
                $limit,
                empty($type) ? array() : array('type' => $type)
            );
        } elseif ($relation == 'learned') {
            $total   = $this->getCourseService()->findUserLeanedCourseCount($user['id'], $conditions);
            $courses = $this->getCourseService()->findUserLeanedCourses(
                $user['id'],
                $start,
                $limit,
                empty($type) ? array() : array('type' => $type)
            );
        } elseif ($relation == 'learn') {
            $total              = $this->getCourseService()->findUserLearnCourseCount($user['id'], true);
            $coursesAfterColumn = $this->getCourseService()->findUserLearnCourses(
                $user['id'],
                $start,
                $limit,
                empty($type) ? array() : array('type' => $type)
            );
            $courses = array_values($coursesAfterColumn);
        } elseif ($relation == 'teaching') {
            $total   = $this->getCourseService()->findUserTeachCourseCount(array('userId' => $user['id']), false);
            $courses = $this->getCourseService()->findUserTeachCourses(
                array('userId' => $user['id']),
                $start,
                $limit,
                false
            );
        } elseif ($relation == 'favorited') {
            $total   = $this->getCourseService()->findUserFavoritedCourseCount($user['id']);
            $courses = $this->getCourseService()->findUserFavoritedCourses(
                $user['id'],
                $start,
                $limit
            );
        } else {
            return $this->error('error', '缺少参数!');
        }

        return $this->wrap($this->filter($courses), $total);
    }

    public function filter($res)
    {
        return $this->multicallFilter('Course', $res);
    }

    protected function multicallFilter($name, $res)
    {
        foreach ($res as $key => $one) {
            $res[$key] = $this->callFilter($name, $one);
        }

        return $res;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
