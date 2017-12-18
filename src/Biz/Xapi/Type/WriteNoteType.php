<?php

namespace Biz\Xapi\Type;

class WriteNoteType extends Type
{
    const TYPE = 'noted_note';

    public function package($statement)
    {
        $note = $this->getCourseNoteService()->getNote($statement['target_id']);
        $task = $this->getTaskService()->getTask($note['taskId']);
        $course = $this->getCourseService()->getCourse($note['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($note['courseSetId']);
        $course['description'] = $courseSet['subtitle'];
        $course['title'] = $courseSet['title'].'-'.$course['title'];
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        if (in_array($activity['mediaType'], array('video', 'audio', 'doc', 'ppt', 'flash'))) {
            $resource = $this->getUploadFileService()->getFile($activity['ext']['mediaId']);
        }

        $object = array(
            'id' => $note['id'],
            'course' => $course,
            'definitionType' => $this->convertMediaType($task['type']),
            'resource' => empty($resource) ? array() : $resource,
        );

        $actor = $this->getActor($statement['user_id']);

        $result = $note;

        return $this->createXAPIService()->writeNote($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
    }

    public function packages($statements)
    {
        // TODO: Implement packages() method.
    }
}
