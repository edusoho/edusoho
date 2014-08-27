<?php
namespace Topxia\WebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;

class ClassController extends BaseController
{
    public function showAction(Request $request, $classId)
    {
        return $this->forward('TopxiaWebBundle:ClassThread:list', array('classId' => $classId), $request->query->all());
    }

}