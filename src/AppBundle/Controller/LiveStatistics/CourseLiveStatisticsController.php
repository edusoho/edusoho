<?php

namespace AppBundle\Controller\LiveStatistics;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Live\LiveStatisticsException;
use Biz\Live\Service\LiveStatisticsService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;

class CourseLiveStatisticsController extends BaseController
{
    public function indexAction(Request $request, $courseSetId, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        $taskConditions = [
            'courseId' => $courseId,
            'fromCourseSetId' => $courseSetId,
            'type' => 'live',
            'titleLike' => $request->query->get('title'),
            'status' => 'published',
        ];

        $paginator = new Paginator(
            $request,
            $this->getTaskService()->countTasks($taskConditions),
            10
        );

        $liveTasks = $this->getTaskService()->searchTasks(
            $taskConditions,
            ['seq' => 'ASC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render(
            'course-manage/live/live-statistics.html.twig',
            [
                'course' => $course,
                'courseSet' => $courseSet,
                'liveTasks' => $liveTasks,
                'paginator' => $paginator,
            ]
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
            [
                'courseSet' => $courseSet,
                'course' => $course,
                'task' => $task,
                'activity' => $activity,
            ]
        );
    }

    public function modalAction(Request $request, $taskId, $liveId, $type)
    {
        $task = $this->getTaskService()->tryTakeTask($taskId);
        $status = $request->query->get('status');

        if (LiveStatisticsService::STATISTICS_TYPE_CHECKIN == $type) {
            $statistics = $this->getLiveStatisticsService()->getCheckinStatisticsByLiveId($liveId);

            if ($status && !empty($statistics['data']['detail'])) {
                $groupedStatistics = ArrayToolkit::group($statistics['data']['detail'], 'checkin');
                $groupedStatistics = [
                    empty($groupedStatistics[0]) ? [] : $groupedStatistics[0],
                    empty($groupedStatistics[1]) ? [] : $groupedStatistics[1],
                ];

                $statistics['data']['detail'] = 'checked' == $status ? $groupedStatistics[1] : $groupedStatistics[0];
            }
        } else {
            $statistics = $this->getLiveStatisticsService()->getVisitorStatisticsByLiveId($liveId);
        }

        $statistics = empty($statistics['data']['detail']) ? [] : $statistics['data']['detail'];

        $paginator = new Paginator(
            $request,
            count($statistics),
            10
        );

        $statistics = array_slice($statistics, $paginator->getOffsetCount(), $paginator->getPerPageCount());

        $userIds = ArrayToolkit::column($statistics, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

        return $this->render('course-manage/live/live-statistics-modal.html.twig', [
            'liveId' => $liveId,
            'task' => $task,
            'statistics' => $statistics,
            'type' => $type,
            'paginator' => $paginator,
            'status' => $status,
            'users' => $users,
            'profiles' => $profiles,
        ]);
    }

    public function jsonDataAction(Request $request, $taskId, $liveId)
    {
        $this->getTaskService()->tryTakeTask($taskId);

        try {
            $checkin = $this->getLiveStatisticsService()->updateCheckinStatistics($liveId);
        } catch (LiveStatisticsException $e) {
            $checkin['errorCode'] = $e->getCode();
        }

        try {
            $visitor = $this->getLiveStatisticsService()->updateVisitorStatistics($liveId);
        } catch (LiveStatisticsException $e) {
            $visitor['errorCode'] = $e->getCode();
        }

        if (!empty($checkin['data']['time'])) {
            $checkin['data']['time'] = date('Y-m-d H:i:s', $checkin['data']['time']);
        }

        if (!empty($visitor['data']['totalLearnTime'])) {
            $visitor['data']['totalLearnTime'] = ceil($visitor['data']['totalLearnTime'] / 60);
        }

        return $this->createJsonResponse([
            'checkin' => $checkin,
            'visitor' => $visitor,
        ]);
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
