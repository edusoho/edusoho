<?php
/**
 * User: Edusoho V8
 * Date: 26/10/2016
 * Time: 19:25
 */

namespace WebBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

class VideoActivityController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id)
    {
        // TODO: Implement showAction() method.
    }

    public function editAction(Request $request, $id)
    {
        // TODO: Implement editAction() method.
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('WebBundle:VideoActivity:modal.html.twig', array(
            'courseId' => $courseId
        ));
    }


}