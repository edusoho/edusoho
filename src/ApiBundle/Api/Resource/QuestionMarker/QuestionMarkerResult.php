<?php

namespace ApiBundle\Api\Resource\QuestionMarker;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\MemberException;

class QuestionMarkerResult extends AbstractResource
{
    public function add(ApiRequest $request, $taskId)
    {
        $canLearn = $this->getCourseService()->canLearnTask($taskId);
        if ('success' != $canLearn['code']) {
            throw MemberException::NOTFOUND_MEMBER();
        }

        $data = $request->request->all();
        $result = $this->getQuestionMarkerResultService()->finishQuestionMarker($data['markerItemId'], array(
            'userId' => $this->getCurrentUser()['id'],
            'taskId' => $taskId,
            'answer' => $data['item_response']['question_responses'][0]['response'],
        ));

        $returnData = $data['item_response'];
        $returnData['question_responses'][0]['status'] = $result['status'];
        $returnData['question_reports'] = $returnData['question_responses'];
        unset($returnData['question_responses']);

        return $returnData;
    }

    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    protected function getQuestionMarkerResultService()
    {
        return $this->service('Marker:QuestionMarkerResultService');
    }
}
