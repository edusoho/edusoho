<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class CourseController extends BaseController
{

    public function nicknameCheckAction(Request $request, $courseId)
    {
        $nickname = $request->query->get('value');
        $result = $this->getUserService()->isNicknameAvaliable($nickname);
        if ($result) {
            $response = array('success' => false, 'message' => '该用户还不是注册用户！');
        } else {
            $user = $this->getUserService()->getUserByNickname($nickname);
            $isCourseStudent = $this->getCourseService()->isCourseStudent($courseId, $user['id']);
            if($isCourseStudent){
                $response = array('success' => false, 'message' => '该用户已是本课程的学员了，请不要重复添加！');
            } else {
                $response = array('success' => true, 'message' => '该用户尚未学习本课程，可以添加！');
            }
            
            $isCourseTeacher = $this->getCourseService()->isCourseTeacher($courseId, $user['id']);
            if($isCourseTeacher){
                $response = array('success' => false, 'message' => '该用户是本课程的教师，不需要添加成学员!');
            }
        }
        return $this->createJsonResponse($response);
    }

    public function indexAction (Request $request)
    {
        $conditions = $request->query->all();

        $count = $this->getCourseService()->searchCourseCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $courses = $this->getCourseService()->searchCourses($conditions, null, $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        return $this->render('TopxiaAdminBundle:Course:index.html.twig', array(
            'conditions' => $conditions,
            'courses' => $courses ,
            'users' => $users,
            'categories' => $categories,
            'paginator' => $paginator
        ));
    }

    public function addStudentAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);
        $currentUser = $this->getCurrentUser();
        if ('POST' == $request->getMethod()) {
            $courseUrl = $this->generateUrl('course_show', array('id'=>$course['id']), true);
            $formData = $request->request->all();
            $user = $this->getUserService()->getUserByNickname($formData['nickname']);
            $this->getCourseService()->joinCourse($user['id'], $course['id']);
            $this->getNotificationService()->notify($user['id'], 'default',
                "管理员{$currentUser['nickname']}已将你设置为课程<a href='{$courseUrl}' target='_blank'>《{$course['title']}》</a> 的学员了, 开始学习吧!");
            return $this->redirect($this->generateUrl('admin_course'));
        }

        return $this->render('TopxiaAdminBundle:Course:add-student-modal.html.twig',array(
            'course'=>$course));
    }

    public function deleteAction(Request $request, $id)
    {
        $result = $this->getCourseService()->deleteCourse($id);
        return $this->createJsonResponse(true);
    }

    public function publishAction(Request $request, $id)
    {
        $this->getCourseService()->publishCourse($id);
        return $this->renderCourseTr($id);
    }

    public function closeAction(Request $request, $id)
    {
        $this->getCourseService()->closeCourse($id);
        return $this->renderCourseTr($id);
    }

    public function recommendAction(Request $request, $id)
    {
        $course = $this->getCourseService()->recommendCourse($id);
        return $this->renderCourseTr($id);
    }

    public function cancelRecommendAction(Request $request, $id)
    {
        $course = $this->getCourseService()->cancelRecommendCourse($id);
        return $this->renderCourseTr($id);
    }


    public function categoryAction(Request $request)
    {
        return $this->forward('TopxiaAdminBundle:Category:embed', array(
            'group' => 'course',
            'layout' => 'TopxiaAdminBundle:Course:layout.html.twig',
        ));
    }

    private function renderCourseTr($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        return $this->render('TopxiaAdminBundle:Course:tr.html.twig', array(
            'user' => $this->getUserService()->getUser($course['userId']),
            'category' => $this->getCategoryService()->getCategory($course['categoryId']),
            'course' => $course ,
        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }
}