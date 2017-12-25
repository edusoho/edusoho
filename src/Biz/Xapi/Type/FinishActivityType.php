<?php

namespace Biz\Xapi\Type;

use AppBundle\Common\ArrayToolkit;

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

        return $this->createXAPIService()->finishActivity($actor, $object, array(), $statement['uuid'], $statement['occur_time'], false);
    }

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }
        try {
            $taskResultIds = ArrayToolkit::column($statements, 'target_id');
            $taskResults = $this->getTaskResultService()->searchTaskResults(array('ids' => $taskResultIds), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
            $taskResults = ArrayToolkit::index($taskResults, 'id');

            $taskIds = ArrayToolkit::column($taskResults, 'courseTaskId');
            $tasks = $this->getTaskService()->findTasksByIds($taskIds);
            $tasks = ArrayToolkit::index($tasks, 'id');

            $courseIds = ArrayToolkit::column($taskResults, 'courseId');
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

            $activityIds = ArrayToolkit::column($taskResults, 'activityId');
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
                    $taskResult = $taskResults[$statement['target_id']];
                    $task = $tasks[$taskResult['courseTaskId']];
                    $course = $courses[$taskResult['courseId']];
                    $activity = $activities[$taskResult['activityId']];
                    $resource = empty($resources[$activity['ext']['mediaId']]) ? array() : $resources[$activity['ext']['mediaId']];

                    $actor = $this->getActor($statement['user_id']);
                    $object = array(
                        'id' => $task['id'],
                        'name' => $task['title'],
                        'course' => $course,
                        'resource' => empty($resource) ? array() : $resource,
                        'definitionType' => $this->convertMediaType($activity['mediaType']),
                    );

                    $pushStatements[] = $sdk->finishActivity($actor, $object, array(), $statement['uuid'], $statement['occur_time'], false);
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
