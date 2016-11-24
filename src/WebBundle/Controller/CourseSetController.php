<?php

namespace WebBundle\Controller;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class CourseSetController extends BaseController
{
    public function createAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            var_dump($data);
            $courseSet = $this->getCourseSetService()->createCourseSet($data);
            return $this->redirect($this->generateUrl('courseset_manage', array(
                'id' => $courseSet['id']
            )));
        }
        $user        = $this->getUser();
        $userProfile = $this->getUserService()->getUserProfile($user->getId());
        $user        = $this->getUserService()->getUser($user->getId());
        return $this->render('WebBundle:CourseSet:create.html.twig', array(
            'user'        => $user,
            'userProfile' => $userProfile
        ));
    }

    public function showAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);
        $courseId  = $request->query->get('courseId', 0);
        $course    = array();
        if ($courseId > 0) {
            $course = $this->getCourseService()->getCourse($courseId);
        } else {
            $course = $this->getCourseService()->getDefaultCourseByCourseSetId($id);
        }
        //do other things
        return $this->render('WebBundle:CourseSet:show.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course
        ));
    }

    public function previewAction(Request $request, $id)
    {
        $courseId = $request->query->get('courseId', 0);
        $course   = array();
        if ($courseId > 0) {
            $course = $this->getCourseService()->getCourse($courseId);
        } else {
            $course = $this->getCourseService()->getDefaultCourseByCourseSetId($id);
        }
        $courseSet = $this->getCourseSetService()->getCourseSet($id);
        return $this->render('WebBundle:CourseSet:preview.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course
        ));
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }
}
