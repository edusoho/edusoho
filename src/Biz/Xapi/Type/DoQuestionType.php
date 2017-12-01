<?php

namespace Biz\Xapi\Type;

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
