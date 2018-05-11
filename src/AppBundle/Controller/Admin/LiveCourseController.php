<?php

namespace AppBundle\Controller\Admin;

use Biz\Task\Service\TaskService;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;

class LiveCourseController extends BaseController
{
    public function indexAction(Request $request, $status)
    {
        $eduCloudStatus = $this->getEduCloudStatus();

        $default = $this->getSettingService()->get('default', array());

        $taskConditions = $request->query->all();
        $taskConditions['type'] = 'live';

        if (!empty($taskConditions['keywordType']) && !empty($taskConditions['keyword'])) {
            if ('courseSetTitle' == $taskConditions['keywordType']) {
                $courseSets = $this->getCourseSetsByKeyWord($taskConditions['keyword']);
                if (empty($courseSets)) {
                    return $this->render(
                        'admin/live-course/index.html.twig',
                        array(
                            'status' => $status,
                            'liveTasks' => array(),
                            'courseSets' => array(),
                            'paginator' => array(),
                            'default' => $default,
                            'eduCloudStatus' => $eduCloudStatus,
                        )
                    );
                }
                $taskConditions['fromCourseSetIds'] = ArrayToolkit::column($courseSets, 'id');
            }

            if ('taskTitle' == $taskConditions['keywordType']) {
                $taskConditions['titleLike'] = $taskConditions['keyword'];
            }
            unset($taskConditions['keywordType']);
            unset($taskConditions['keyword']);
        }

        list($taskConditions, $orderBy) = $this->getConditionAndOrderByStatus($status, $taskConditions);

        $paginator = new Paginator(
            $request,
            $this->getTaskService()->countTasks($taskConditions),
            20
        );

        $liveTasks = $this->getTaskService()->searchTasks(
            $taskConditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if (!isset($courseSets)) {
            $courseSetIds = ArrayToolkit::column($liveTasks, 'fromCourseSetId');
            $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
        }

        $this->migrate($courseSets, $liveTasks);

        return $this->render(
            'admin/live-course/index.html.twig',
            array(
                'status' => $status,
                'liveTasks' => $liveTasks,
                'courseSets' => $courseSets,
                'paginator' => $paginator,
                'default' => $default,
                'eduCloudStatus' => $eduCloudStatus,
            )
        );
    }

    private function getCourseSetsByKeyWord($word)
    {
        $courseSetConditions['title'] = $word;
        $courseSetConditions = $this->fillOrgCode($courseSetConditions);
        $courseSets = $this->getCourseSetService()->searchCourseSets($courseSetConditions, array(), 0, PHP_INT_MAX);

        return ArrayToolkit::index($courseSets, 'id');
    }

    public function getMaxOnlineAction(Request $request)
    {
        $conditions = $request->query->all();

        if (!empty($conditions['courseId']) && !empty($conditions['lessonId'])) {
            $lesson = $this->getCourseService()->getCourseLesson($conditions['courseId'], $conditions['lessonId']);

            $client = new EdusohoLiveClient();

            if ('live' == $lesson['type']) {
                $result = $client->getMaxOnline($lesson['mediaId']);
                $lesson = $this->getCourseService()->setCourseLessonMaxOnlineNum($lesson['id'], $result['onLineNum']);
            }
        }

        return $this->createJsonResponse($lesson);
    }

    private function getConditionAndOrderByStatus($status, $conditions)
    {
        $orderBy = array('startTime' => 'ASC');
        if ('coming' == $status) {
            $conditions['startTime_GT'] = time();
        }

        if ('underway' == $status) {
            $conditions['startTime_LE'] = time();
            $conditions['endTime_GT'] = time();
        }

        if ('end' == $status) {
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
        $status = $this->getEduCloudService()->isVisibleCloud();
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

    /**
     * @return TaskService
     */
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
