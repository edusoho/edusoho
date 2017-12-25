<?php

namespace Biz\Xapi\Type;

use AppBundle\Common\ArrayToolkit;

class VideoWatchType extends Type
{
    const TYPE = 'watch_video';

    public function package($statement)
    {
        $watchLog = $this->getXapiService()->getWatchLog($statement['target_id']);
        $task = $this->getTaskService()->getTask($watchLog['task_id']);
        $course = $this->getCourseService()->getCourse($watchLog['course_id']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $course['description'] = $courseSet['subtitle'];
        $course['title'] = $courseSet['title'].'-'.$course['title'];
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        if (in_array($activity['mediaType'], array('video', 'audio', 'doc', 'ppt', 'flash'))) {
            $resource = $this->getUploadFileService()->getFile($activity['ext']['mediaId']);
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

        return $this->createXAPIService()->watchVideo($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
    }

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }
        try {
            $watchingLogIds = ArrayToolkit::column($statements, 'target_id');
            $watchingLogs = $this->getXapiService()->findWatchLogsByIds($watchingLogIds);
            $watchingLogs = ArrayToolkit::index($watchingLogs, 'id');

            $taskIds = ArrayToolkit::column($watchingLogs, 'task_id');
            $tasks = $this->getTaskService()->findTasksByIds($taskIds);
            $tasks = ArrayToolkit::index($tasks, 'id');

            $courseIds = ArrayToolkit::column($watchingLogs, 'course_id');
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

            $resourceIds = array();
            foreach ($activities as $activity) {
                if (in_array($activity['mediaType'], array('video', 'audio', 'doc', 'ppt', 'flash'))) {
                    if (!empty($activity['ext']['mediaId'])) {
                        $resourceIds[] = $activity['ext']['mediaId'];
                    }
                }
            }
            $resources = $this->getUploadFileService()->findFilesByIds($resourceIds);
            $resources = ArrayToolkit::index($resources, 'id');

            $sdk = $this->createXAPIService();
            $pushStatements = array();

            foreach ($statements as $statement) {
                try {
                    $watchLog = $watchingLogs[$statement['target_id']];
                    $course = $courses[$watchLog['course_id']];
                    $task = $tasks[$watchLog['task_id']];
                    $activity = $activities[$task['activityId']];
                    $resource = empty($resources[$activity['ext']['mediaId']]) ? array() : $resources[$activity['ext']['mediaId']];
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
