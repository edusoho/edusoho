<?php

namespace Biz\Xapi\Type;

use AppBundle\Common\ArrayToolkit;

class AskQuestionType extends Type
{
    const TYPE = 'asked_question';

    public function package($statement)
    {
        $thread = $this->getThreadService()->getThread(0, $statement['target_id']);
        if ('question' != $thread['type']) {
            return;
        }
        $task = $this->getTaskService()->getTask($thread['taskId']);
        $course = $this->getCourseService()->getCourse($thread['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($thread['courseSetId']);
        $course['description'] = $courseSet['subtitle'];
        $course['title'] = $courseSet['title'].'-'.$course['title'];
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        if (in_array($activity['mediaType'], array('video', 'audio', 'doc', 'ppt', 'flash'))) {
            $resource = $this->getUploadFileService()->getFile($activity['ext']['mediaId']);
        }

        $object = array(
            'id' => $thread['id'],
            'course' => $course,
            'definitionType' => $this->convertMediaType($task['type']),
            'resource' => empty($resource) ? array() : $resource,
        );

        $actor = $this->getActor($statement['user_id']);

        $result = $thread;

        return $this->createXAPIService()->askQuestion($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
    }

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }
        try {
            $threadIds = ArrayToolkit::column($statements, 'target_id');
            $threads = $this->getThreadService()->searchThreads(array('ids' => $threadIds, 'type' => 'question'), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
            $threads = ArrayToolkit::index($threads, 'id');

            $taskIds = ArrayToolkit::column($threads, 'taskId');
            $tasks = $this->getTaskService()->findTasksByIds($taskIds);
            $tasks = ArrayToolkit::index($tasks, 'id');

            $courseSetIds = ArrayToolkit::column($threads, 'courseSetId');
            $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
            $courseSets = ArrayToolkit::index($courseSets, 'id');

            $courseIds = ArrayToolkit::column($threads, 'courseId');
            $courses = $this->getCourseService()->findCoursesByIds($courseIds);
            $courses = ArrayToolkit::index($courses, 'id');
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
                    $thread = $threads[$statement['target_id']];
                    $course = $courses[$thread['courseId']];
                    $task = $tasks[$thread['taskId']];
                    $activity = $activities[$task['activityId']];
                    $resource = empty($resources[$activity['ext']['mediaId']]) ? array() : $resources[$activity['ext']['mediaId']];
                    $object = array(
                        'id' => $thread['id'],
                        'course' => $course,
                        'definitionType' => $this->convertMediaType($task['type']),
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
