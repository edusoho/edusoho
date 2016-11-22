<?php
/**
 * User: Edusoho V8
 * Date: 03/11/2016
 * Time: 10:05
 */

namespace WebBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

class DownLoadActivityController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        // TODO: Implement showAction() method.
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity  = $this->getActivityService()->getActivity($id);
        $materials = array();

        foreach ($activity['ext']['materials'] as $media) {
            $id             = empty($media['fileId']) ? $media['link'] : $media['fileId'];
            $materials[$id] = array('id' => $media['fileId'], 'size' => $media['fileSize'], 'name' => $media['title'], 'link' => $media['link']);
        }
        $activity['ext']['materials'] = $materials;
        return $this->render('WebBundle:DownLoadActivity:modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('WebBundle:DownLoadActivity:modal.html.twig', array(
            'courseId' => $courseId
        ));
    }

    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

}