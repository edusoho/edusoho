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
        $noteIds = ArrayToolkit::column($statements, 'target_id');
        $notes = $this->getCourseNoteService()->searchNotes(array('ids' => $noteIds), array('id' => 'DESC'), 0, PHP_INT_MAX);
        $notes = ArrayToolkit::index($notes, 'id');

        $taskIds = ArrayToolkit::column($notes, 'taskId');
        $tasks = $this->getTaskService()->findTasksByIds($taskIds);
        $tasks = ArrayToolkit::index($tasks, 'id');

        $courseIds = ArrayToolkit::column($notes, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, 'id');

        $courseSetIds = ArrayToolkit::column($notes, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
        $courseSets = ArrayToolkit::index($courseSets, 'id');
        foreach ($courses as &$course) {
            $course['description'] = empty($courseSet['subtitle']) ? '' : $courseSet['subtitle'];
            $course['title'] = $courseSet['title'].'-'.$course['title'];
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
                $note = $notes[$statement['target_id']];
                $course = $courses[$note['courseId']];
                $task = $tasks[$note['taskId']];
                $activity = $activities[$task['activityId']];
                $resource = empty($resources[$activity['ext']['mediaId']]) ? array() : $resources[$activity['ext']['mediaId']];
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
    }
}
