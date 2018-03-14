<?php

namespace Biz\Xapi\Type;

class AskQuestionType extends Type
{
    const TYPE = 'asked_question';

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }
        try {
            $threads = $this->findCourseThreads(
                array($statements, 'target_id'),
                array('type' => 'question')
            );

            $tasks = $this->findTasks(
                array($threads, 'taskId')
            );

            $courses = $this->findCourses(
                array($threads, 'courseId')
            );

            list($activities, $resources) = $this->findActivities(
                array($tasks, 'activityId')
            );

            $sdk = $this->createXAPIService();
            $pushStatements = array();

            foreach ($statements as $statement) {
                try {
                    $thread = $threads[$statement['target_id']];
                    $course = $courses[$thread['courseId']];
                    $task = empty($tasks[$thread['taskId']]) ? array() : $tasks[$thread['taskId']];
                    $activity = (empty($task) || empty($activities[$task['activityId']])) ? array() : $activities[$task['activityId']];
                    if (!empty($activity) && !empty($activity['ext']['mediaId'])) {
                        $resource = empty($resources[$activity['ext']['mediaId']]) ? array() : $resources[$activity['ext']['mediaId']];
                    } else {
                        $resource = array();
                    }

                    $object = array(
                        'id' => $thread['id'],
                        'course' => $course,
                        'definitionType' => empty($task['type']) ? 'course' : $this->convertMediaType($task['type']),
                        'resource' => empty($resource) ? array() : $resource,
                    );
                    $actor = $this->getActor($statement['user_id']);

                    $result = $thread;
                    $pushStatements[] = $sdk->askQuestion($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
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
