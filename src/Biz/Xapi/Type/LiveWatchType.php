<?php

namespace Biz\Xapi\Type;

use AppBundle\Common\ArrayToolkit;

class LiveWatchType extends Type
{
    const TYPE = 'watch_live';

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }
        try {
            $watchLogs = $this->findActivityWatchLogs(
                array($statements, 'target_id')
            );

            $tasks = $this->findTasks(
                array($watchLogs, 'task_id')
            );

            $courses = $this->findCourses(
                array($watchLogs, 'course_id')
            );

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
