<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class MarkerController extends BaseController
{
    public function manageAction(Request $request, $id)
    {
        $user   = $this->getCurrentUser();
        $lesson = $this->getCourseService()->getLesson($id);
        return $this->render('TopxiaWebBundle:Marker:index.html.twig', array(
            'lesson' => $lesson
        ));
    }

    public function addQuestionMarker(Request $request)
    {
        $data = $request->request->all();

        $data['questionId'] = isset($data['questionId']) ? $data['questionId'] : 0;
        $question           = $this->getQuestionService()->getQuestion($data['questionId']);

        if (empty($question)) {
            return $this->createMessageResponse('error', '该题目不存在!');
        }

        if (!isset($data['markerId'])) {
            $fields = array(
                'second' => $data['second']
            );
            $marker   = $this->getMarkerService()->addMarker($data['mediaId'], $fields);
            $question = $this->getQuestionMarkerService()->addQuestionMarker($data['qusetionId'], $marker['id'], 1);
            return $this->createJsonResponse($question);
        } else {
            $marker = $this->getMarkerService()->getMarker($data['markerId']);

            if (!empty($marker)) {
                $question  = $this->getQuestionMarkerService()->addQuestionMarker($data['qusetionId'], $marker['id'], $data['seq']);
                $questions = $this->getQuestionMarkerService()->updateQuestionMarkerSeq($question['seq']);
                return $this->createJsonResponse($question);
            } else {
                return $this->createJsonResponse(false);
            }
        }
    }

    public function deleteQuestionMarker(Request $request)
    {
        $data               = $request->request->all();
        $data['questionId'] = isset($data['questionId']) ? $data['questionId'] : 0;
        $result             = $this->getMarkerService()->deleteQuestionMarker($data['questionId']);
        return $this->createJsonResponse($result);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getMarkerService()
    {
        return $this->getServiceKernel()->createService('Marker.MarkerService');
    }

    protected function getQuestionMarkerService()
    {
        return $this->getServiceKernel()->createService('Marker.QuestionMarkerService');
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }
}
