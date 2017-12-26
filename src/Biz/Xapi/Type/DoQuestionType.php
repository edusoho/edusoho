<?php

namespace Biz\Xapi\Type;

use AppBundle\Common\ArrayToolkit;

class DoQuestionType extends Type
{
    const TYPE = 'answered_question';

    public function package($statement)
    {
        $questionMarkerResult = $this->getQuestionMarkerResultService()->getQuestionMarkerResult($statement['target_id']);

        $questionMarker = $this->getQuestionMarkerService()->getQuestionMarker($questionMarkerResult['questionMarkerId']);
        $answers = array();
        if (is_array($questionMarker['answer'])) {
            foreach ($questionMarker['answer'] as $answer) {
                $answers[] = $this->num_to_capital($answer);
            }
        }

        $choices = array();
        if (isset($questionMarker['metas']['choices'])) {
            foreach ($questionMarker['metas']['choices'] as $id => $choice) {
                $choices[] = array(
                    'id' => $id,
                    'description' => array(
                        'zh-CN' => $this->num_to_capital($id),
                    ),
                );
            }
        }

        $task = $this->getTaskService()->getTask($questionMarkerResult['taskId']);

        $course = $this->getCourseService()->getCourse($task['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $course['description'] = $courseSet['subtitle'];
        $course['title'] = $courseSet['title'].'-'.$course['title'];
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        if (in_array($activity['mediaType'], array('video', 'audio', 'doc', 'ppt', 'flash'))) {
            $resource = $this->getUploadFileService()->getFile($activity['ext']['mediaId']);
        }

        $actor = $this->getActor($statement['user_id']);
        $object = array(
            'id' => $questionMarker['id'],
            'type' => $this->convertQuestionType($questionMarker['type']),
            'stem' => $questionMarker['stem'],
            'answer' => $answers,
            'choices' => $choices,
            'course' => $course,
            'activity' => $activity,
            'resource' => empty($resource) ? array() : $resource,
        );

        $result = array(
            'response' => implode(',', $answers),
            'success' => ('right' == $questionMarkerResult['status']) ? true : false,
        );

        return $this->createXAPIService()->finishActivityQuestion($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
    }

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }
        try {
            $questionMarkerResultIds = ArrayToolkit::column($statements, 'target_id');
            $questionMarkerResults = $this->getQuestionMarkerResultService()->findResultsByIds($questionMarkerResultIds);
            $questionMarkerResults = ArrayToolkit::index($questionMarkerResults, 'id');

            $questionMarkerIds = ArrayToolkit::column($questionMarkerResults, 'questionMarkerId');
            $questionMarkers = $this->getQuestionMarkerService()->findQuestionMarkersByIds($questionMarkerIds);
            $questionMarkers = ArrayToolkit::index($questionMarkers, 'id');

            $taskIds = ArrayToolkit::column($questionMarkerResults, 'taskId');
            $tasks = $this->getTaskService()->findTasksByIds($taskIds);
            $tasks = ArrayToolkit::index($tasks, 'id');

            $courseIds = ArrayToolkit::column($tasks, 'courseId');
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
                    $questionMarkerResult = $questionMarkerResults[$statement['target_id']];
                    $questionMarker = $questionMarkers[$questionMarkerResult['questionMarkerId']];
                    $task = $tasks[$questionMarkerResult['taskId']];
                    $course = $courses[$task['courseId']];
                    $activity = $activities[$task['activityId']];
                    if (!empty($activity['ext']['mediaId'])) {
                        $resource = empty($resources[$activity['ext']['mediaId']]) ? array() : $resources[$activity['ext']['mediaId']];
                    } else {
                        $resource = array();
                    }

                    $answers = array();
                    if (is_array($questionMarker['answer'])) {
                        foreach ($questionMarker['answer'] as $answer) {
                            $answers[] = $this->num_to_capital($answer);
                        }
                    }

                    $choices = array();
                    if (isset($questionMarker['metas']['choices'])) {
                        foreach ($questionMarker['metas']['choices'] as $id => $choice) {
                            $choices[] = array(
                                'id' => $id,
                                'description' => array(
                                    'zh-CN' => $this->num_to_capital($id),
                                ),
                            );
                        }
                    }

                    $actor = $this->getActor($statement['user_id']);
                    $object = array(
                        'id' => $questionMarker['id'],
                        'type' => $this->convertQuestionType($questionMarker['type']),
                        'stem' => $questionMarker['stem'],
                        'answer' => $answers,
                        'choices' => $choices,
                        'course' => $course,
                        'activity' => $activity,
                        'resource' => empty($resource) ? array() : $resource,
                    );

                    $result = array(
                        'response' => implode(',', $answers),
                        'success' => ('right' == $questionMarkerResult['status']) ? true : false,
                    );

                    $pushStatements[] = $sdk->finishActivityQuestion($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
                } catch (\Exception $e) {
                    $this->biz['logger']->error($e);
                }
            }

            return $pushStatements;
        } catch (\Exception $e) {
            $this->biz['logger']->error($e);
        }
    }

    public function convertQuestionType($questionType)
    {
        $types = array(
            'single_choice' => 'choice',
            'choice' => 'choice',
            'fill' => 'fill-in',
            'material' => 'material',
            'essay' => 'essay',
            'determine' => 'true-false',
        );

        return empty($types[$questionType]) ? $questionType : $types[$questionType];
    }
}
