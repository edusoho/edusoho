<?php

namespace WebBundle\Controller;

class CourseController extends BaseController
{
    public function createAction(Request $request)
    {
        //create page

        return $this->render('WebBundle:Course:create.html.twig', array(
            //courseSetId..
        ));
    }

    public function previewAction(Request $request, $courseSetId, $courseId)
    {
        //preview course
    }

    public function listAction(Request $request)
    {
        //list courses
    }

    public function marketingAction(Request $request, $courseSetId, $courseId)
    {
        //营销设置
    }

    public function preparePublishment(Request $request, $courseSetId, $courseId)
    {
        //提交发布审核
    }

    public function auditPublishment(Request $request, $courseSetId, $courseId)
    {
        //管理员进行审核
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }
}
