<?php

namespace AppBundle\Controller\Course;

use Biz\Content\Service\FileService;
use Topxia\Common\ArrayToolkit;
use Biz\Taxonomy\Service\TagService;
use Biz\Course\Service\CourseService;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Biz\OpenCourse\Service\OpenCourseService;
use Symfony\Component\HttpFoundation\Request;

class CourseSetManageController extends BaseController
{
    public function createAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            if(!isset($data['type'])){
                throw $this->createNotFoundException('未设置课程类型');
            }else{
                $type = $data['type'];
            }

            if ($type == 'open') {
                $openCourse = $this->getOpenCourseService()->createCourse($data);
                return $this->redirectToRoute('open_course_manage', array(
                    'id' => $openCourse['id']
                ));
            } else {
                $courseSet = $this->getCourseSetService()->createCourseSet($data);
                return $this->redirect($this->generateUrl('course_set_manage', array(
                    'id' => $courseSet['id']
                )));
            }
        }

        $user        = $this->getUser();
        $userProfile = $this->getUserService()->getUserProfile($user->getId());
        $user        = $this->getUserService()->getUser($user->getId());
        return $this->render('courseset-manage/create.html.twig', array(
            'user'        => $user,
            'userProfile' => $userProfile
        ));
    }

    public function indexAction(Request $request, $id)
    {
        return $this->redirectToRoute('course_set_manage_courses', array(
            'courseSetId' => $id
        ));
    }

    public function headerAction($courseSet, $course = null)
    {
        // $users = empty($courseSet['teacherIds']) ? array() : $this->getUserService()->findUsersByIds($courseSet['teacherIds']);
        //暂时显示课程的创建者
        $courseSet['teacherIds'] = array($courseSet['creator']);
        $users                   = $this->getUserService()->findUsersByIds($courseSet['teacherIds']);
        return $this->render('courseset-manage/header.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course,
            'users'     => $users
        ));
    }

    public function sidebarAction($courseSetId, $curCourse, $sideNav)
    {
        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSetId);
        if (empty($curCourse)) {
            $curCourse = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSetId);
        }
        if (empty($curCourse) && !empty($courses)) {
            $curCourse = current($courses);
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        return $this->render('courseset-manage/sidebar.html.twig', array(
            'courseSet' => $courseSet,
            'curCourse' => $curCourse,
            'courses'   => $courses,
            'side_nav'  => $sideNav
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
            if (!empty($data['goals'])) {
                $data['goals'] = json_decode($data['goals'], true);
            }
            if (!empty($data['audiences'])) {
                $data['audiences'] = json_decode($data['audiences'], true);
            }

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

    public function deleteAction($id)
    {
        try {
            $this->getCourseSetService()->deleteCourseSet($id);
            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    public function publishAction($id)
    {
        try {
            $publishedCourses = $this->getCourseService()->findPublishedCoursesByCourseSetId($id);
            if (empty($publishedCourses)) {
                throw $this->createAccessDeniedException('发布课程时请确保课程下至少有一个已发布的教学计划');
            }
            $this->getCourseSetService()->publishCourseSet($id);
            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    public function closeAction($id)
    {
        try {
            $this->getCourseSetService()->closeCourseSet($id);
            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }
}
