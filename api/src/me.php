<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

$api = $app['controllers_factory'];

//获取当前用户信息
$api->get('/', function (Request $request) {
    $user = convert($request->query->get('token'),'me');
    return filter($user, 'me');
});

//获取当前用户课程
$api->get('/courses', function (Request $request) {
    $conditions = $request->query->all();
    $user = convert($conditions['token'],'me');
    if ($conditions['relation'] == 'learning') {
        $total = ServiceKernel::instance()->createService('Course.CourseService')->findUserLeaningCourseCount($user['id'],$conditions);
        $courses = ServiceKernel::instance()->createService('Course.CourseService')->findUserLeaningCourses(
            $user['id'], 
            $conditions['start'], 
            $conditions['limit'], 
            array('type' => $conditions['type'])
        );
    } elseif ($conditions['relation'] == 'learned') {
        $total = ServiceKernel::instance()->createService('Course.CourseService')->findUserLeanedCourseCount($user['id'],$conditions);
        $courses = ServiceKernel::instance()->createService('Course.CourseService')->findUserLeanedCourses(
            $user['id'], 
            $conditions['start'], 
            $conditions['limit'], 
            array('type' => $conditions['type'])
        );
    } elseif ($conditions['relation'] == 'teaching') {
        $total = ServiceKernel::instance()->createService('Course.CourseService')->findUserTeachCourseCount(array('userId' => $user['id']), false);
        $courses = ServiceKernel::instance()->createService('Course.CourseService')->findUserTeachCourses(
            array('userId' => $user['id']),
            $conditions['start'], 
            $conditions['limit'], 
            false
        );
    } else if ($conditions['relation'] == 'favorited') {
        $total = ServiceKernel::instance()->createService('Course.CourseService')->findUserFavoritedCourseCount($user['id']);
        $courses = ServiceKernel::instance()->createService('Course.CourseService')->findUserFavoritedCourses(
            $user['id'],
            $conditions['start'], 
            $conditions['limit']
        );
    } else {
        //全部
        // $courses = array();
        // $total = 0;
    }
    return array(
        'data' => $courses,
        'total' => $total
    );
});

return $api;