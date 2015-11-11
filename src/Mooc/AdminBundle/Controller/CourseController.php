<?php
namespace Mooc\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\CourseController as BaseController;

class CourseController extends BaseController
{
    public function deleteAction(Request $request, $courseId, $type)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isSuperAdmin()) {
            throw $this->createAccessDeniedException('您不是超级管理员！');
        }

        //判断作业插件版本号
        $homework = $this->getAppService()->findInstallApp("Homework");
        if(!empty($homework)){
           $isDeleteHomework = $homework && version_compare($homework['version'], "1.0.1", ">=");
            if(!$isDeleteHomework){
                return $this->createJsonResponse(array('code' =>1, 'message' => '作业插件未升级'));
            } 
        }

        $course = $this->getCourseService()->getCourse($courseId);
        if ($course['status'] == 'published') {
            throw $this->createAccessDeniedException('发布课程，不能删除！');
        }

        $subCourses = $this->getCourseService()->findCoursesByParentIdAndLocked($courseId, 1);
        if (!empty($subCourses)) {
            return $this->createJsonResponse(array('code' => 2, 'message' => '请先删除班级课程'));
        }

        $classroomCourse = $this->getClassroomService()->findClassroomIdsByCourseId($course['id']);
        if ($classroomCourse) {
            return $this->createJsonResponse(array('code' => 3, 'message' => '当前课程未移除,请先移除班级课程'));
        }

        if ($course['status'] == 'draft') {
            $result = $this->getCourseService()->deleteCourse($courseId);
            return $this->createJsonResponse(array('code' => 0, 'message' => '删除课程成功'));
        }

        if ($course['status'] == 'closed') {
            if ($type) {
                $isCheckPassword = $request->getSession()->get('checkPassword');
                if (!$isCheckPassword) {
                    throw $this->createAccessDeniedException('未输入正确的校验密码！');
                }
                $result = $this->getCourseDeleteService()->delete($courseId, $type);
                return $this->createJsonResponse($this->returnDeleteStatus($result, $type));
            }
        }

        return $this->render('TopxiaAdminBundle:Course:delete.html.twig', array('course' => $course));
    }
}
