<?php

namespace Biz\Xapi\Type;

use AppBundle\Common\ArrayToolkit;

class FinishActivityType extends Type
{
    const TYPE = 'finish_activity';

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }
        try {
            $taskResultIds = ArrayToolkit::column($statements, 'target_id');
            $taskResults = $this->getTaskResultService()->searchTaskResults(array('ids' => $taskResultIds), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
            $taskResults = ArrayToolkit::index($taskResults, 'id');

            $tasks = $this->findTasks(
                array($taskResults, 'courseTaskId')
            );

            $courses = $this->findCourses(
                array($tasks, 'courseId')
            );

            list($activities, $resources) = $this->findActivities(
                array($tasks, 'activityId')
            );

            $sdk = $this->createXAPIService();
            $pushStatements = array();

            foreach ($statements as $statement) {
                try {
                    $taskResult = $taskResults[$statement['target_id']];
                    $task = $tasks[$taskResult['courseTaskId']];
                    $course = $courses[$taskResult['courseId']];
                    $activity = $activities[$taskResult['activityId']];
                    if (!empty($activity['ext']['mediaId'])) {
                        $resource = empty($resources[$activity['ext']['mediaId']]) ? array() : $resources[$activity['ext']['mediaId']];
                    } else {
                        $resource = array();
                    }
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
