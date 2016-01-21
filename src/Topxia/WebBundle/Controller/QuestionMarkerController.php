<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class QuestionMarkerController extends BaseController
{
    public function sortQuestionAction(Request $Request, $markerId)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $ids  = $data['ids'];
            $this->getQuestionMarkerService()->sortQuestionMarkers($ids);
            return $this->createJsonResponse(true);
        }

        $marker = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerId($markerId);
        //返回twig为某一个驻点的所有问题
        return $this->render('TopxiaWebBundle:Marker:question-marker-modal.html.twig', array(
            'marker' => $marker
        ));
    }

    protected function getQuestionMarkerService()
    {
        return $this->getServiceKernel()->createService('Marker.QuestionMarkerService');
    }
}
