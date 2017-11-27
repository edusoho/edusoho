<?php

namespace Biz\Xapi\Type;

class FinishActivityType extends Type
{
    const TYPE = 'finish_activity';

    public function package($statement)
    {
        $taskResult = $this->getTaskResultService()->getTaskResult($statement['target_id']);
        $course = $this->getCourseService()->getCourse($taskResult['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $course['description'] = $courseSet['subtitle'];
        $course['title'] = $courseSet['title'].'-'.$course['title'];

        $activity = $this->getActivityService()->getActivity($taskResult['activityId'], true);
        if (in_array($activity['mediaType'], array('video', 'audio', 'doc', 'ppt', 'flash'))) {
            $resource = $this->getUploadFileService()->getFile($activity['ext']['mediaId']);
        }

        $task = $this->getTaskService()->getTask($taskResult['courseTaskId']);

        $actor = $this->getActor($statement['user_id']);
        $object = array(
            'id' => $task['id'],
            'name' => $task['title'],
            'course' => $course,
            'resource' => empty($resource) ? array() : $resource,
            'definitionType' => $this->convertMediaType($activity['mediaType']),
        );

        return $this->createXAPIService()->finishActivity($actor, $object, array(), $statement['uuid'], $statement['created_time'], false);
    }
}
