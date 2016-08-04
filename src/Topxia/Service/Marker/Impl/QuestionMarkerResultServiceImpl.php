<?php
namespace Topxia\Service\Marker\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Marker\QuestionMarkerResultService;

class QuestionMarkerResultServiceImpl extends BaseService implements QuestionMarkerResultService
{
    public function getQuestionMarkerResult($id)
    {
        return $this->getQuestionMarkerResultDao()->getQuestionMarkerResult($id);
    }

    public function addQuestionMarkerResult($result)
    {
        $result['createdTime'] = time();

        return $this->getQuestionMarkerResultDao()->addQuestionMarkerResult($result);
    }

    public function updateQuestionMarkerResult($id, $result)
    {
        $result['updatedTime'] = time();

        return $this->getQuestionMarkerResultDao()->updateQuestionMarkerResult($id, $result);
    }

    public function finishCurrentQuestion($markerId, $userId, $questionMarkerId, $answer, $type, $lessonId)
    {
        $questionMarker = $this->getQuestionMarkerService()->getQuestionMarker($questionMarkerId);
        if (in_array($type, array('single_choice', 'determine'))) {
            $status = array_diff($answer, $questionMarker['answer']) ? 'right' : 'wrong';
        }

        if ($type == 'uncertain_choice') {
            if (array_diff($questionMarker['answer'], $answer) || array_diff($answer, $questionMarker['answer'])) {
                if (array_diff($questionMarker['answer'], $answer) && !array_diff($answer, $questionMarker['answer'])) {
                    $status = 'partRight';
                } else {
                    $status = 'wrong';
                }
            } else {
                $status = 'right';
            }
        }

        if ($type == 'fill') {
            foreach ($questionMarker['answer'] as $key => $questionMarkerAnswer) {
                $status = in_array($answer, $questionMarkerAnswer) ? 'right' : 'wrong';
            }
        }

        if ($type == 'choice') {
            if (array_diff($questionMarker['answer'], $answer) && array_diff($answer, $questionMarker['answer'])) {
                $status = 'wrong';
            } else {
                $status = 'right';
            }
        }

        $questionMarkerResult = $this->findByUserIdAndQuestionMarkerId($userId, $questionMarkerId);
        return $this->addQuestionMarkerResult(array(
            'markerId'         => $markerId,
            'questionMarkerId' => $questionMarkerId,
            'lessonId'         => $lessonId,
            'userId'           => $userId,
            'status'           => $status,
            'answer'           => serialize($answer),
            'createdTime'      => time(),
            'updatedTime'      => time()
        ));
    }

    public function deleteByQuestionMarkerId($questionMarkerId)
    {
        return $this->getQuestionMarkerResultDao()->deleteByQuestionMarkerId($questionMarkerId);
    }

    public function findByUserIdAndMarkerId($userId, $markerId)
    {
        return $this->getQuestionMarkerResultDao()->findByUserIdAndMarkerId($userId, $markerId);
    }

    public function findByUserIdAndQuestionMarkerId($userId, $questionMarkerId)
    {
        return $this->getQuestionMarkerResultDao()->findByUserIdAndQuestionMarkerId($userId, $questionMarkerId);
    }

    protected function getQuestionMarkerResultDao()
    {
        return $this->createDao('Marker.QuestionMarkerResultDao');
    }

    protected function getQuestionMarkerService()
    {
        return $this->createService('Marker.QuestionMarkerService');
    }
}
