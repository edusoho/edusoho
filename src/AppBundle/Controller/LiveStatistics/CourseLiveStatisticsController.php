<?php

namespace AppBundle\Controller\LiveStatistics;

use AppBundle\Common\ArrayToolkit;
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

    public function modalAction(Request $request, $taskId, $liveId, $type)
    {
        $task = $this->getTaskService()->getTask($taskId);

        if (LiveStatisticsService::STATISTICS_TYPE_CHECKIN == $type) {
            $statistics = $this->getLiveStatisticsService()->getCheckinStatisticsByLiveId($liveId);
        } else {
            $statistics = $this->getLiveStatisticsService()->getVisitorStatisticsByLiveId($liveId);
        }

        $statistics = empty($statistics['data']['detail']) ? array() : $statistics['data']['detail'];

        $paginator = new Paginator(
            $request,
            count($statistics),
            10
        );

        $statistics = array_slice($statistics, $paginator->getOffsetCount(), $paginator->getPerPageCount());

        return $this->render('course-manage/live/live-statistics-modal.html.twig', array(
            'liveId' => $liveId,
            'task' => $task,
            'statistics' => $statistics,
            'type' => $type,
            'paginator' => $paginator,
        ));
    }

    public function checkinDataAction(Request $request, $liveId)
    {
        $status = $request->query->get('status');

        $statistics = $this->getLiveStatisticsService()->getCheckinStatisticsByLiveId($liveId);

        $statistics = empty($statistics['data']['detail']) ? array() : $statistics['data']['detail'];

        if ($status) {
            $groupedStatistics = ArrayToolkit::group($statistics, 'checkin');
            $groupedStatistics = array(
                empty($groupedStatistics[0]) ? array() : $groupedStatistics[0],
                empty($groupedStatistics[1]) ? array() : $groupedStatistics[1],
            );

            $statistics = $status == 'checked' ? $groupedStatistics[1] : $groupedStatistics[0];
        }

        $paginator = new Paginator(
            $request,
            count($statistics),
            10
        );

        $statistics = array_slice($statistics, $paginator->getOffsetCount(), $paginator->getPerPageCount());

        return $this->render('course-manage/live/checkin-data.html.twig', array(
            'statistics' => $statistics,
            'paginator' => $paginator,
        ));
    }

    public function jsonDataAction(Request $request, $liveId)
    {
        $checkin = $this->getLiveStatisticsService()->updateCheckinStatistics($liveId);
        $visitor = $this->getLiveStatisticsService()->updateVisitorStatistics($liveId);

        if (!empty($checkin['data']['time'])) {
            $checkin['data']['time'] = date('Y-m-d H:i:s', $checkin['data']['time']);
        }

        if (!empty($visitor['data']['totalLearnTime'])) {
            $visitor['data']['totalLearnTime'] = ceil($visitor['data']['totalLearnTime'] / 60);
        }

        return $this->createJsonResponse(array(
            'checkin' => $checkin,
            'visitor' => $visitor,
        ));
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
