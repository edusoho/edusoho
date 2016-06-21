<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;

class LiveCourseController extends BaseController
{
    public function indexAction(Request $request, $status)
    {

        $default = $this->getSettingService()->get('default', array());

        $query = $request->query->all();

        $courseCondition = array(
            'type'   => 'live',
            'status' => 'published'
        );

        $courseCondition = array_merge($courseCondition, $query);

        if (!empty($query['keywordType']) && !empty($query['keyword'])) {
            if ($query['keywordType'] == 'courseTitle') {
                $courseCondition['title'] = $query['keyword'];
            }

            if ($query['keywordType'] == 'lessonTitle') {
                $conditions['title'] = $query['keyword'];
            }
        }

        $courseCondition = $this->fillOrgCode($courseCondition);
        $courses   = $this->getCourseService()->searchCourses($courseCondition, $sort = 'latest', 0, 1000);
        $courseIds = ArrayToolkit::column($courses, 'id');
        if (empty($courseIds)) {
            return $this->render('TopxiaAdminBundle:LiveCourse:index.html.twig', array(
                'status'    => $status,
                'lessons'   => array(),
                'courses'   => array(),
                'paginator' => new Paginator(
                    $request,
                    0,
                    20
                ),
                'default'   => $default
            ));
        }

        $conditions['courseIds'] = $courseIds;

        $conditions['type'] = "live";

        if ($status == 'coming') {
            $conditions['startTimeGreaterThan'] = !empty($query['startDateTime']) ? strtotime($query['startDateTime']) : time();
            $conditions['startTimeLessThan']    = !empty($query['endDateTime']) ? strtotime($query['endDateTime']) : null;
        }

        if ($status == 'end') {
            $conditions['endTimeLessThan']      = time();
            $conditions['startTimeLessThan']    = !empty($query['endDateTime']) ? strtotime($query['endDateTime']) : null;
            $conditions['startTimeGreaterThan'] = !empty($query['startDateTime']) ? strtotime($query['startDateTime']) : null;
        }

        if ($status == 'underway') {
            $conditions['endTimeGreaterThan']   = time();
            $conditions['startTimeLessThan']    = !empty($query['endDateTime']) ? strtotime($query['endDateTime']) : time();
            $conditions['startTimeGreaterThan'] = !empty($query['startDateTime']) ? strtotime($query['startDateTime']) : null;
        }

        $conditions['status'] = 'published';

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->searchLessonCount($conditions),
            20
        );

        if ($status == 'end') {
            $lessons = $this->getCourseService()->searchLessons($conditions,
                array('startTime', 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        } else {
            $lessons = $this->getCourseService()->searchLessons($conditions,
                array('startTime', 'ASC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }

        return $this->render('TopxiaAdminBundle:LiveCourse:index.html.twig', array(
            'status'    => $status,
            'lessons'   => $lessons,
            'courses'   => ArrayToolkit::index($courses, 'id'),
            'paginator' => $paginator,
            'default'   => $default
        ));
    }

    public function getMaxOnlineAction(Request $request)
    {
        $conditions = $request->query->all();

        if (!empty($conditions['courseId']) && !empty($conditions['lessonId'])) {
            $lesson = $this->getCourseService()->getCourseLesson($conditions['courseId'], $conditions['lessonId']);

            $client = new EdusohoLiveClient();

            if ($lesson['type'] == 'live') {
                $result = $client->getMaxOnline($lesson['mediaId']);
                $lesson = $this->getCourseService()->setCourseLessonMaxOnlineNum($lesson['id'], $result['onLineNum']);
            }
        }

        return $this->createJsonResponse($lesson);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
