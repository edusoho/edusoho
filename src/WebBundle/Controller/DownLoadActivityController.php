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
        $activity = $this->getActivityService()->getActivity($id);

       /* var_dump($activity);
        {"size":39884539,"length":0,"source":"self","status":"none","name":"Whats New in macOS Sierra Beta 3.mp4","id":206}
        {"source":"link","id":"http://x7.edusoho.com/course/462/manage/lesson","name":"http://x7.edusoho.com/course/462/manage/lesson","link":"http://x7.edusoho.com/course/462/manage/lesson"}*/

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