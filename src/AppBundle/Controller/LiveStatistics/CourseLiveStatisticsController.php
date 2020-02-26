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

    public function checkinStatisticsAction(Request $request, $courseId, $liveId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $statistics = $this->getLiveStatisticsService()->updateCheckinStatistics($liveId);

        return $this->render('course-manage/live/checkin-statistics.html.twig', array(
            'statistics' => $statistics,
            'course' => $course,
        ));
    }

    public function visitorStatisticsAction(Request $request, $courseId, $liveId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $statistics = $this->getLiveStatisticsService()->updateVisitorStatistics($liveId);

        if ($statistics['data']['totalLearnTime']) {
            $statistics['data']['averageLearnTime'] = ceil($statistics['data']['totalLearnTime'] / 1000 / 60 / $course['studentNum']);
        }

        return $this->render('course-manage/live/visitor-statistics.html.twig', array(
            'statistics' => $statistics,
            'course' => $course,
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
