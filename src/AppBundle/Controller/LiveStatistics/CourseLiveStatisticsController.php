<?php

namespace AppBundle\Controller\LiveStatistics;

use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Live\Service\LiveStatisticsService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;

class CourseLiveStatisticsController extends BaseController
{
    public function indexAction(Request $request, $courseSetId, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        $taskConditions = array(
            'courseId' => $courseId,
            'fromCourseSetId' => $courseSetId,
            'type' => 'live',
            'titleLike' => $request->query->get('title'),
            'status' => 'published',
        );

        $paginator = new Paginator(
            $request,
            $this->getTaskService()->countTasks($taskConditions),
            10
        );

        $liveTasks = $this->getTaskService()->searchTasks(
            $taskConditions,
            array('seq' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render(
            'course-manage/live/live-statistics.html.twig',
            array(
                'course' => $course,
                'courseSet' => $courseSet,
                'liveTasks' => $liveTasks,
                'paginator' => $paginator,
            )
        );
    }

    public function detailAction(Request $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $task = $this->getTaskService()->getCourseTask($courseId, $taskId);

        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        return $this->render(
            'course-manage/live/live-statistics-detail.html.twig',
            array(
                'courseSet' => $courseSet,
                'course' => $course,
                'task' => $task,
                'activity' => $activity,
            )
        );
    }

    public function jsonDataAction(Request $request, $liveId)
    {
        $checkin = $this->getLiveStatisticsService()->updateCheckinStatistics($liveId);
        $visitor = $this->getLiveStatisticsService()->updateVisitorStatistics($liveId);

        if (!empty($checkin['data']['time'])) {
            $checkin['data']['time'] = date('Y-m-d H:i:s', $checkin['data']['time'] / 1000);
        }

        $visitor['data'] = $this->dealWithVisitorData($visitor['data']);

        return $this->createJsonResponse(array(
            'checkin' => $checkin,
            'visitor' => $visitor,
        ));
    }

    private function dealWithVisitorData($visitorData)
    {
        if (!empty($visitorData['totalLearnTime'])) {
            $visitorData['totalLearnTime'] = ceil($visitorData['totalLearnTime'] / 1000 / 60);
        }

        foreach ($visitorData['detail'] as &$user) {
            $user['firstJoin'] = empty($user['firstJoin']) ? '0' : date('Y-m-d H:i:s', $user['firstJoin'] / 1000);
            $user['lastLeave'] = empty($user['lastLeave']) ? '0' : date('Y-m-d H:i:s', $user['lastLeave'] / 1000);
            $user['learnTime'] = empty($user['learnTime']) ? '0' : $user['learnTime'] / 1000 / 60;
        }

        return $visitorData;
    }

    /**
     * @return LiveStatisticsService
     */
    protected function getLiveStatisticsService()
    {
        return $this->createService('Live:LiveStatisticsService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
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

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
