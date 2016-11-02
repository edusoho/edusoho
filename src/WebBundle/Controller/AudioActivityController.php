<?php
/**
 * User: Edusoho V8
 * Date: 02/11/2016
 * Time: 14:03
 */

namespace WebBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

class AudioActivityController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id)
    {
        // TODO: Implement showAction() method.
    }

    public function editAction(Request $request, $id, $courseId)
    {
        // TODO: Implement editAction() method.
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('WebBundle:AudioActivity:modal.html.twig', array(
            'courseId' => $courseId
        ));
    }


}