<?php

namespace AppBundle\Controller\Callback\Marketing;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;

class CourseSetController extends MarketingBaseController
{
    public function indexAction(Request $request)
    {
        $keywords = $request->query->get('q');

        if (empty($keywords)) {
            return array();
        }
        $conditions = array(
            'status' => 'published',
            'parentId' => '0',
        );
        $conditions['title'] = $keywords;
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('updatedTime' => 'desc'),
            0,
            5
        );
        $courseSets = ArrayToolkit::index($courseSets, 'id');

        $courseSetIds = ArrayToolkit::column($courseSets, 'id');
        $courses = $this->getCourseService()->findCoursesByCourseSetIds($courseSetIds);
        $results = array();
        foreach ($courses  as $courseId => $course) {
            if ($course['status'] != 'published') {
                continue;
            }
            $courseSet = $courseSets[$course['courseSetId']];
            $result = array();
            $result['id'] = $course['id'];
            $courseCover = $courseSet['cover'] ? $courseSet['cover']['small'] : '';
            $result['cover'] = $this->getWebExtension()->getFurl($courseCover, 'course.png');
            if ($course['title'] == '默认教学计划') {
                $result['title'] = '《'.$courseSet['title'].'》';
            } else {
                $result['title'] = '课程《'.$courseSet['title'].'》的教学计划'.$course['title'];
            }
            $results[] = $result;
        }

        return $results;
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
