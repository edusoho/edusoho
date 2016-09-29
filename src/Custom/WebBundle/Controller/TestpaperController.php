<?php
namespace Custom\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\TestpaperController as BaseTestpaperController;

class TestpaperController extends BaseTestpaperController
{
    public function teacherCheckInCourseAction(Request $request, $id, $status)
    {
        $user = $this->getCurrentUser();
        if (in_array('ROLE_CENTER_ADMIN', $user->getRoles())) {
            $this->addTeacherRoleForCenterAdmin($user , $id);
        }
        $users = $this->getUserService()->findUsersByOrgCode($user['orgCode']);
        $userIds = ArrayToolkit::column($users, 'id');

        $course = $this->getCourseService()->tryManageCourse($id);

        $testpapers = $this->getTestpaperService()->findAllTestpapersByTarget($id);

        $testpaperIds = ArrayToolkit::column($testpapers, 'id');

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->findTestPaperResultCountByStatusAndTestIdsAndUserIds($testpaperIds, $status, $userIds),
            10
        );

        $testpaperResults = $this->getTestpaperService()->findTestPaperResultsByStatusAndTestIdsAndUserIds(
            $testpaperIds,
            $status,
            $userIds,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($testpaperResults, 'userId'));

        $teacherIds = ArrayToolkit::column($testpaperResults, 'checkTeacherId');

        $teachers = $this->getUserService()->findUsersByIds($teacherIds);

        return $this->render('TopxiaWebBundle:MyQuiz:list-course-test-paper.html.twig', array(
            'status'       => $status,
            'testpapers'   => ArrayToolkit::index($testpapers, 'id'),
            'paperResults' => ArrayToolkit::index($testpaperResults, 'id'),
            'course'       => $course,
            'users'        => $users,
            'teachers'     => ArrayToolkit::index($teachers, 'id'),
            'paginator'    => $paginator,
            'isTeacher'    => $this->getCourseService()->hasTeacherRole($id, $user['id']) || $user->isSuperAdmin()
        ));
    }
    
    public function finishTestAction(Request $request, $id)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);
    
        if (!empty($testpaperResult) && !in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return $this->createJsonResponse(true);
        }
    
        if ($request->getMethod() == 'POST') {
            $data     = $request->request->all();
            $answers  = array_key_exists('data', $data) ? $data['data'] : array();
            $usedTime = $data['usedTime'];
            $user     = $this->getCurrentUser();
    
            //提交变化的答案
            $results = $this->getTestpaperService()->submitTestpaperAnswer($id, $answers);
    
            //完成试卷，计算得分
            $testResults = $this->getTestpaperService()->makeTestpaperResultFinish($id);
    
            $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);
    
            $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);
            //试卷信息记录
            $this->getTestpaperService()->finishTest($id, $user['id'], $usedTime);
    
            $targets = $this->get('topxia.target_helper')->getTargets(array($testpaper['target']));
    
            if ($this->getTestpaperService()->isExistsEssay($testResults)) {
                $user = $this->getCurrentUser();
    
                $message = array(
                    'id'       => $testpaperResult['id'],
                    'name'     => $testpaperResult['paperName'],
                    'userId'   => $user['id'],
                    'userName' => $user['nickname'],
                    'type'     => 'perusal'
                );
                
                $admins = $this->getUserService()->findCenterOrSuperAdminUsersByOrgId($user['orgId']);
                foreach ($admins as $admin) {
                    $this->getNotificationService()->notify($admin['id'], 'test-paper', $message);
                }
                
            }
    
            // @todo refactor. , wellming
            $targets = $this->get('topxia.target_helper')->getTargets(array($testpaperResult['target']));
    
            if ($targets[$testpaperResult['target']]['type'] == 'lesson' && !empty($targets[$testpaperResult['target']]['id'])) {
                $lessons = $this->getCourseService()->findLessonsByIds(array($targets[$testpaperResult['target']]['id']));
    
                if (!empty($lessons[$targets[$testpaperResult['target']]['id']])) {
                    $lesson = $lessons[$targets[$testpaperResult['target']]['id']];
                    $this->getCourseService()->finishLearnLesson($lesson['courseId'], $lesson['id']);
                }
            }
    
            return $this->createJsonResponse(true);
        }
    }
    
    public function teacherCheckAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        if (in_array('ROLE_CENTER_ADMIN', $user->getRoles())) {
            $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);
            $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);
            
            if (!$testpaper) {
                throw $this->createNotFoundException($this->getServiceKernel()->trans('试卷不存在'));
            }
            
            $target = explode('-', $testpaper['target']);
            
            if ($target[0] == 'course') {
                $courseIds = explode('/', $target[1]);
                $this->addTeacherRoleForCenterAdmin($user , $courseIds[0]);
            }
        }
        return parent::teacherCheckAction($request, $id);
    }
    
    private function addTeacherRoleForCenterAdmin($user , $courseId)
    {
        if (! $this->getCourseService()->hasTeacherRole($courseId, $user['id'])) {
            $courseTeachers = $this->getCourseService()->findCourseTeachers($courseId);
            $teacherIds = ArrayToolkit::column($courseTeachers, 'userId');
            $teacherIds[] = $user['id'];
            $teachers = $this->getUserService()->findUsersByIds($teacherIds);
            $this->getCourseService()->setCourseTeachers($courseId, $teachers);
        }
    }
}