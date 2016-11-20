<?php

namespace WebBundle\Controller;

class CourseSetController extends BaseController
{
    public function createAction(Request $request)
    {
        return $this->render('WebBundle:CourseSet:create.html.twig', array(
            //params
        ));
    }

    public function previewAction(Request $request, $courseSetId)
    {
        // 预览courseSet
    }

    public function deleteAction(Request $request, $courseSetId)
    {
        //delete..
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }
}
