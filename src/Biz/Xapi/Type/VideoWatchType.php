<?php

namespace Biz\Xapi\Type;

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

        return $this->createXAPIService()->watchVideo($actor, $object, $result, $statement['uuid'], $statement['created_time'], false);
    }
}
