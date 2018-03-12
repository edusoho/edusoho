<?php

namespace Biz\Xapi\Type;

use AppBundle\Common\ArrayToolkit;

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
            'id' => $activity['id'],
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
        if (empty($statements)) {
            return array();
        }
        try {
            $noteIds = ArrayToolkit::column($statements, 'target_id');
            $notes = $this->getCourseNoteService()->searchNotes(array('ids' => $noteIds), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
            $notes = ArrayToolkit::index($notes, 'id');

            $tasks = $this->findTasks(
                array($notes, 'taskId')
            );

            $courses = $this->findCourses(
                array($notes, 'courseId')
            );

            list($activities, $resources) = $this->findActivities(
                array($tasks, 'activityId')
            );

            $sdk = $this->createXAPIService();
            $pushStatements = array();

            foreach ($statements as $statement) {
                try {
                    $note = $notes[$statement['target_id']];
                    $course = $courses[$note['courseId']];
                    $task = $tasks[$note['taskId']];
                    $activity = $activities[$task['activityId']];
                    if (!empty($activity['ext']['mediaId'])) {
                        $resource = empty($resources[$activity['ext']['mediaId']]) ? array() : $resources[$activity['ext']['mediaId']];
                    } else {
                        $resource = array();
                    }
                    $object = array(
                        'id' => $activity['id'],
                        'course' => $course,
                        'definitionType' => $this->convertMediaType($task['type']),
                        'resource' => empty($resource) ? array() : $resource,
                    );
                    $actor = $this->getActor($statement['user_id']);
                    $result = $note;
                    $pushStatements[] = $sdk->writeNote($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
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
