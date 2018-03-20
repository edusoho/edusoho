<?php

namespace Biz\Xapi\Type;

class VideoWatchType extends Type
{
    const TYPE = 'watch_video';

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }
        try {
            $watchingLogs = $this->findActivityWatchLogs(
                array($statements, 'target_id')
            );

            $tasks = $this->findTasks(
                array($watchingLogs, 'task_id')
            );

            $courses = $this->findCourses(
                array($watchingLogs, 'course_id')
            );

            list($activities, $resources) = $this->findActivities(
                array($tasks, 'activityId')
            );

            $sdk = $this->createXAPIService();
            $pushStatements = array();

            foreach ($statements as $statement) {
                try {
                    $watchLog = $watchingLogs[$statement['target_id']];
                    $course = $courses[$watchLog['course_id']];
                    $task = $tasks[$watchLog['task_id']];
                    $activity = $activities[$task['activityId']];
                    if (!empty($activity['ext']['mediaId'])) {
                        $resource = empty($resources[$activity['ext']['mediaId']]) ? array() : $resources[$activity['ext']['mediaId']];
                    } else {
                        $resource = array();
                    }
                    $object = array(
                        'id' => $activity['id'],
                        'name' => $task['title'],
                        'course' => $course,
                        'definitionType' => $this->convertMediaType($task['type']),
                        'resource' => empty($resource) ? array() : $resource,
                    );
                    $actor = $this->getActor($statement['user_id']);
                    $result = array(
                        'duration' => $watchLog['watched_time'],
                    );
                    $pushStatements[] = $sdk->watchVideo($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
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
