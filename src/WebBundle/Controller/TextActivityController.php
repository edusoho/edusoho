<?php
/**
 * User: retamia
 * Date: 2016/10/24
 * Time: 13:27
 */

namespace WebBundle\Controller;


use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class TextActivityController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id)
    {

    }

    public function editAction(Request $request, $id)
    {
        $activity = $this->getActivityService()->getActivity($id);
        return $this->render('WebBundle:TextActivity:modal.html.twig', array(
            'activity'    => $activity
        ));
    }

    public function createAction(Request $request)
    {
        return $this->render('WebBundle:TextActivity:modal.html.twig');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }
}