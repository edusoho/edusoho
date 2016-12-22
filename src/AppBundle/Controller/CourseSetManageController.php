<?php

namespace AppBundle\Controller;

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
        return $this->render('courseset/create.html.twig', array(
            'user'        => $user,
            'userProfile' => $userProfile
        ));
    }

    public function indexAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        $courses   = $this->getCourseService()->findCoursesByCourseSetId($id);
        return $this->render('courseset-manage/courses.html.twig', array(
            'courseSet' => $courseSet,
            'courses'   => $courses
        ));
    }

    public function headerAction($courseSet)
    {
        $users = empty($courseSet['teacherIds']) ? array() : $this->getUserService()->findUsersByIds($courseSet['teacherIds']);

        return $this->render('courseset-manage/header.html.twig', array(
            'courseSet' => $courseSet,
            'users'     => $users
        ));
    }

    public function sidebarAction($courseSetId, $sideNav)
    {
        return $this->render('courseset-manage/sidebar.html.twig', array(
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
            return $this->redirect($this->generateUrl('course_set_manage_base', array('id' => $id)));
        }

        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        $tags      = array();
        if (!empty($courseSet['tags'])) {
            $tags = $this->getTagService()->findTagsByIds($courseSet['tags']);
        }
        return $this->render('courseset-manage/base.html.twig', array(
            'courseSet' => $courseSet,
            'tags'      => ArrayToolkit::column($tags, 'name')
        ));
    }

    public function detailAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getCourseSetService()->updateCourseSetDetail($id, $data);
            return $this->redirect($this->generateUrl('course_set_manage_detail', array('id' => $id)));
        }
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        return $this->render('courseset-manage/detail.html.twig', array(
            'courseSet' => $courseSet
        ));
    }

    public function coverAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getCourseSetService()->changeCourseSetCover($id, $data);
            return $this->redirect($this->generateUrl('course_set_manage_cover', array('id' => $id)));
        }

        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        // if ($courseSet['cover']) {
        //     $courseSet['cover'] = json_decode($courseSet['cover'], true);
        // }
        return $this->render('courseset-manage/cover.html.twig', array(
            'courseSet' => $courseSet
        ));
    }

    public function coverCropAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $this->getCourseSetService()->changeCourseSetCover($courseSet['id'], json_decode($data["images"], true));
            return $this->redirect($this->generateUrl('course_set_manage_cover', array('id' => $courseSet['id'])));
        }

        $fileId = $request->getSession()->get("fileId");

        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 480, 270);

        return $this->render('courseset-manage/cover-crop.html.twig', array(
            'courseSet'   => $courseSet,
            'pictureUrl'  => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize'  => $scaledSize
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
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }
}
