<?php

namespace Biz\Xapi\Type;

use AppBundle\Common\ArrayToolkit;

class LiveWatchType extends Type
{
    const TYPE = 'watch_live';

    public function package($statement)
    {
        $watchLog = $this->getXapiService()->getWatchLog($statement['target_id']);
        $task = $this->getTaskService()->getTask($watchLog['task_id']);
        $course = $this->getCourseService()->getCourse($watchLog['course_id']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $course['description'] = $courseSet['subtitle'];
        $course['title'] = $courseSet['title'].'-'.$course['title'];
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        $object = array(
            'id' => $activity['id'],
            'name' => $task['title'],
            'course' => $course,
            'definitionType' => $this->convertMediaType($task['type']),
        );

        $actor = $this->getActor($statement['user_id']);

        $result = array(
            'duration' => $watchLog['watched_time'],
        );

        return $this->createXAPIService()->watchLive($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
    }

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }
        try {
            $watchLogIds = ArrayToolkit::column($statements, 'target_id');
            $watchLogs = $this->getXapiService()->findWatchLogsByIds($watchLogIds);
            $watchLogs = ArrayToolkit::index($watchLogs, 'id');

            $taskIds = ArrayToolkit::column($watchLogs, 'task_id');
            $tasks = $this->getTaskService()->findTasksByIds($taskIds);
            $tasks = ArrayToolkit::index($tasks, 'id');

            $courseIds = ArrayToolkit::column($watchLogs, 'course_id');
            $courses = $this->getCourseService()->findCoursesByIds($courseIds);
            $courses = ArrayToolkit::index($courses, 'id');

            $courseSetIds = ArrayToolkit::column($courses, 'courseSetId');
            $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
            $courseSets = ArrayToolkit::index($courseSets, 'id');
            foreach ($courses as &$course) {
                if (!empty($courseSets[$course['courseSetId']])) {
                    $courseSet = $courseSets[$course['courseSetId']];
                    $course['description'] = empty($courseSet['subtitle']) ? '' : $courseSet['subtitle'];
                    $course['title'] = $courseSet['title'].'-'.$course['title'];
                }
            }

            $activityIds = ArrayToolkit::column($tasks, 'activityId');
            $activities = $this->getActivityService()->findActivities($activityIds, true);
            $activities = ArrayToolkit::index($activities, 'id');

            $sdk = $this->createXAPIService();
            $pushStatements = array();

            foreach ($statements as $statement) {
                try {
                    $watchLog = $watchLogs[$statement['target_id']];
                    $course = $courses[$watchLog['course_id']];
                    $task = $tasks[$watchLog['task_id']];
                    $activity = $activities[$task['activityId']];
                    $object = array(
                        'id' => $activity['id'],
                        'name' => $task['title'],
                        'course' => $course,
                        'definitionType' => $this->convertMediaType($task['type']),
                    );
                    $actor = $this->getActor($statement['user_id']);
                    $result = array(
                        'duration' => $watchLog['watched_time'],
                    );
                    $pushStatements[] = $sdk->watchLive($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
                } catch (\Exception $e) {
                    $this->biz['logger']->error($e);
                }
            }

            return $pushStatements;
        } catch (\Exception $e) {
            $this->biz['logger']->error($e);
        }
    }
}
