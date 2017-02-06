<?php
namespace AppBundle\Controller\Admin;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Biz\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;

class LiveCourseController extends BaseController
{
    public function indexAction(Request $request, $status)
    {
        $eduCloudStatus = $this->getEduCloudStatus();

        $default = $this->getSettingService()->get('default', array());

        $taskConditions = $request->query->all();

        $courseConditions = array(
            'type'   => 'live',
            'status' => 'published'
        );

        $courseConditions = array_merge($courseConditions, $taskConditions);

        if (!empty($taskConditions['keywordType']) && !empty($taskConditions['keyword'])) {
            if ($taskConditions['keywordType'] == 'courseSetTitle') {
                $courseConditions['title'] = $taskConditions['keyword'];
            }

            if ($taskConditions['keywordType'] == 'taskTitle') {
                $taskConditions['titleLike'] = $taskConditions['keyword'];
            }
        }

        $courseConditions = $this->fillOrgCode($courseConditions);
        $courseSets   = $this->getCourseSetService()->searchCourseSets($courseConditions, array(), 0, 10000);
        $courseSetIds = ArrayToolkit::column($courseSets, 'id');
        $taskConditions['fromCourseSetIds'] = $courseSetIds;

        $taskConditions['type'] = "live";
        $taskConditions['status'] = 'published';

        list($taskConditions, $orderBy) = $this->getConditionAndOrderByStatus($status, $taskConditions);
        $paginator = new Paginator(
            $request,
            $this->getTaskService()->count($taskConditions),
            20
        );
        $liveTasks = $this->getTaskService()->search(
            $taskConditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $this->migrate($courseSets, $liveTasks);

        return $this->render('admin/live-course/index.html.twig', array(
            'status'    => $status,
            'liveTasks'   => $liveTasks,
            'courseSets'   => ArrayToolkit::index($courseSets, 'id'),
            'paginator' => $paginator,
            'default'   => $default,
            'eduCloudStatus' => $eduCloudStatus
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

    private function getConditionAndOrderByStatus($status, $conditions)
    {
        $orderBy = array('startTime' => 'ASC');
        if ($status == 'coming') {
            $conditions['startTime_GT'] = time();
        }

        if ($status == 'underway') {
            $conditions['startTime_LE'] = time();
            $conditions['endTime_GT'] = time();
        }

        if ($status == 'end') {
            $conditions['endTime_LT'] = time();
            $orderBy = array('startTime' => 'DESC');
        }

        if (!empty($conditions['startDateTime'])) {
            $conditions['startTime_GE'] = strtotime($conditions['startDateTime']);
        }

        if (!empty($conditions['endDateTime'])) {
            $conditions['endTime_GE'] = strtotime($conditions['endDateTime']);
        }

        return array($conditions, $orderBy);
    }

    private function migrate(&$courseSets, &$liveTasks)
    {
        foreach ($courseSets as &$courseSet) {
            $defaultCourse = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSet['id']);
            $courseSet['maxStudentNum'] = $defaultCourse['maxStudentNum'];
        }

        foreach ($liveTasks as &$liveTask) {
            $activity = $this->getActivityService()->getActivity($liveTask['activityId']);
            $liveTask['length'] = $activity['length'];
        }
    }

    private function getEduCloudStatus()
    {
        $status = $this->getEduCloudService()->isHiddenCloud();
        if ($status) {
            $eduCloudStatus = 'open';
        } else {
            $eduCloudStatus = 'closed';
        }

        return $eduCloudStatus;
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getEduCloudService()
    {
        return $this->createService('CloudPlatform:EduCloudService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
