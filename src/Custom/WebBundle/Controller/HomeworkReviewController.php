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
     * @param homeworkResultId , 作业答卷id.
    **/
    public function createAction(Request $request, $homeworkResultId)
    {
        $homeworkResult = $this->getHomeworkService()->loadHomeworkResult($homeworkResultId);

        $canCheckHomework = $this->getHomeworkService()->canCheckHomework($homeworkResult['homeworkId']);
        if (!$canCheckHomework) {
            throw $this->createAccessDeniedException('无权批改作业！');
        }

        $homework = $this->getHomeworkService()->loadHomework($homeworkResult['homeworkId']);
        $course = $this -> getCourseService() -> loadCourse($homework['courseId']);
        $lesson = $this -> getCourseService() -> loadLesson($homework['lessonId']);

        if ($homeworkResult['status'] != 'reviewing') {
            return $this->createMessageResponse('warning', '作业已批阅或者未做完!');
        }

        $items = $this->getHomeworkService()->getItemSetResultByHomeworkIdAndUserId($homeworkResult['homeworkId'],$homeworkResult['userId']);

        if ($request->getMethod() == 'POST') {
            $reviews = $request->request->all();
            $reviews = empty($reviews['data']) ? "" : $reviews['data'];
            $this->getHomeworkService()->createHomeworkReview($homeworkResultId, $this->getCurrentUser()->id,$reviews);
            return $this->createJsonResponse(
                array(
                    'courseId' => $homework['courseId'],
                    'lessonId' => $homework['lessonId']
                )
            );
        }

        return $this->render("CustomWebBundle:HomeworkReview:create.html.twig", array(
            'homework' => $homework,
            'homeworkResult' => $homeworkResult,
            'itemSet' => $items,
            'course' => $course,
            'lesson' => $lesson,
            'userId' => $homeworkResult['userId'],
            'questionStatus' => 'reviewing',
            'targetId' => $request->query->get('targetId'),
            'source' => $request->query->get('source','course'),
            'canCheckHomework' => $canCheckHomework
        ));
    }

    /**
     * 
    **/
    public function checkAction(Request $request, $homeworkResultId)
    {
        $homeworkResult = $this->getHomeworkService()->loadHomeworkResult($homeworkResultId);

        return $this->render('CustomWebBundle:HomeworkReview:check-modal.html.twig',array(
            'homeworkResult' => $homeworkResult,
            'homeworkResultId' => $homeworkResultId,
            'targetId' => $request->query->get('targetId'),
            'source' => $request->query->get('source','course')
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