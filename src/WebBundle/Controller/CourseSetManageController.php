<?php

namespace WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class CourseSetManageController extends BaseController
{
    public function createAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data      = $request->request->all();
            $courseSet = $this->getCourseSetService()->createCourseSet($data);
            return $this->redirect($this->generateUrl('course_set_manage', array(
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

    public function indexAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);
        $courses   = $this->getCourseService()->findCoursesByCourseSetId($id);

        return $this->render('WebBundle:CourseSetManage:courses.html.twig', array(
            'courseSet' => $courseSet,
            'courses'   => $courses
        ));
    }

    public function headerAction($courseSet)
    {
        $users = empty($courseSet['teacherIds']) ? array() : $this->getUserService()->findUsersByIds($courseSet['teacherIds']);

        return $this->render('WebBundle:CourseSetManage:header.html.twig', array(
            'courseSet' => $courseSet,
            'users'     => $users
        ));
    }

    public function sidebarAction($courseSetId, $sideNav)
    {
        return $this->render('WebBundle:CourseSetManage:sidebar.html.twig', array(
            'id'       => $courseSetId,
            'side_nav' => $sideNav
        ));
    }

    //基础信息
    public function baseAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getCourseSetService()->updateCourseSet($id, $data);
        }
        $courseSet = $this->getCourseSetService()->getCourseSet($id);

        $tags = array();
        if (!empty($courseSet['tags'])) {
            $tags = $this->getTagService()->findTagsByIds(explode('|', $courseSet['tags']));
        }
        return $this->render('WebBundle:CourseSetManage:base.html.twig', array(
            'courseSet' => $courseSet,
            'tags'      => ArrayToolkit::column($tags, 'name')
        ));
    }

    public function detailAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);
        return $this->render('WebBundle:CourseSetManage:detail.html.twig', array(
            'courseSet' => $courseSet
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        try {
            $this->getCourseSetService()->deleteCourseSet($id, $this->getUser()->getId());
            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getTagService()
    {
        return ServiceKernel::instance()->createService('Taxonomy.TagService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }
}
