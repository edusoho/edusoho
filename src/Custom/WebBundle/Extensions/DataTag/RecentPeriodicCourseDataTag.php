<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/14
 * Time: 11:41
 */

namespace Custom\WebBundle\Extensions\DataTag;


use Topxia\Service\Course\Impl\CourseServiceImpl;
use Topxia\WebBundle\Extensions\DataTag\CourseBaseDataTag;

class RecentPeriodicCourseByRootIdDataTag extends CourseBaseDataTag
{
    /**
     * @param array $arguments['courseId']
     * @return array $course
     * @throws \Exception
     */
    public function getData(array $arguments)
    {
        $this->checkCourseId($arguments);

        $rootCourse = $this->getCourseService()->getCourse($arguments['courseId']);
        $recentCourse = array();
        if($rootCourse['type'] != 'periodic'){
            throw new \Exception ('该课程不是周期课程！');
        }

        $nowTime = time();



        return $recentCourse;
    }

}