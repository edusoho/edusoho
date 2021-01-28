<?php

namespace Biz\Xapi\Type;

use AppBundle\Common\ArrayToolkit;

class DoQuestionType extends Type
{
    const TYPE = 'answered_question';

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

            $tasks = $this->findTasks(
               array($questionMarkerResults, 'taskId')
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
