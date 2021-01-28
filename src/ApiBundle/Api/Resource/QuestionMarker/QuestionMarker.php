<?php

namespace ApiBundle\Api\Resource\QuestionMarker;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\MemberException;

class QuestionMarker extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $taskId = $request->query->get('taskId');
        $canLearn = $this->getCourseService()->canLearnTask($taskId);
        if ('success' != $canLearn['code']) {
            throw MemberException::NOTFOUND_MEMBER();
        }

        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $user = $this->getCurrentUser();
        $markers = $this->getMarkerService()->findMarkersMetaByMediaId($activity['ext']['mediaId']);
        $results = $this->getQuestionMarkerResultService()->findByUserIdAndMarkerIds($user['id'], ArrayToolkit::column($markers, 'id'));

        return $this->warpperMarkers($markers, $results);
    }

    protected function warpperMarkers($markers, $results)
    {
        $results = ArrayToolkit::group($results, 'markerId');
        foreach ($results as &$result) {
            $result = ArrayToolkit::group($result, 'questionMarkerId');
        }

        foreach ($markers as &$marker) {
            foreach ($marker['questionMarkers'] as &$questionMarker) {
                if (!empty($results[$marker['id']][$questionMarker['id']])) {
                    $questionResult = end($results[$marker['id']][$questionMarker['id']]);
                    $questionMarker['item_report'] = array(
                        'item_id' => $questionMarker['item']['id'],
                        'question_reports' => array(
                            array('question_id' => $questionMarker['item']['questions'][0]['id'], 'status' => $questionResult['status'], 'response' => $questionResult['answer']),
                        ),
                    );
                } else {
                    $questionMarker['item_report'] = (object) array();
                }
            }
        }

        return $markers;
    }

    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    protected function getMarkerService()
    {
        return $this->service('Marker:MarkerService');
    }

    protected function getQuestionMarkerResultService()
    {
        return $this->service('Marker:QuestionMarkerResultService');
    }
}
