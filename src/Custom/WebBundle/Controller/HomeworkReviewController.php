<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\LiveClientFactory;
use Topxia\WebBundle\Controller\BaseController;

/**
 * 作业批改控制器.
**/
class HomeworkReviewController extends BaseController
{
    /**
     * 显示作业批改界面界面.
     * @param request
     * @param courseId,课程id.
     * @param homeworkId , 作业id.
     * @param userId 作业用户id.
    **/
    public function createAction(Request $request, $courseId, $homeworkId, $userId)
    {
        $canCheckHomework = $this->getHomeworkService()->canCheckHomework($homeworkId);
        if (!$canCheckHomework) {
            throw $this->createAccessDeniedException('无权批改作业！');
        }

        $homework = $this->getHomeworkService()->loadHomework($homeworkId);
        $course = $this -> getCourseService() -> loadCourse($homework['courseId']);
        $lesson = $this -> getCourseService() -> loadLesson($homework['lessonId']);

        $homeworkResult = $this->getHomeworkService()->getResultByHomeworkIdAndUserId($homeworkId, $userId);
        if ($homeworkResult['status'] != 'reviewing') {
            return $this->createMessageResponse('warning', '作业已批阅或者未做完!');
        }

        $items = $this->getHomeworkService()->getItemSetResultByHomeworkIdAndUserId($homework['id'],$userId);

        if ($request->getMethod() == 'POST') {
            $checkHomeworkData = $request->request->all();
            $checkHomeworkData = empty($checkHomeworkData['data']) ? "" : $checkHomeworkData['data'];
            $this->getHomeworkService()->checkHomework($homeworkId,$userId,$checkHomeworkData);

            return $this->createJsonResponse(
                array(
                    'courseId' => $courseId,
                    'lessonId' => $homework['lessonId']
                )
            );
        }

        return $this->render("CustomWebBundle:HomeworkReview:create.html.twig", array(
            'homework' => $homework,
            'itemSet' => $items,
            'course' => $course,
            'lesson' => $lesson,
            'userId' => $userId,
            'questionStatus' => 'reviewing',
            'targetId' => $request->query->get('targetId'),
            'source' => $request->query->get('source','course'),
            'canCheckHomework' => $canCheckHomework
        ));
    }

    /**
     * 获取作业服务.
    **/
    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Custom:Homework.HomeworkService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Custom:Course.CourseService');
    }
}