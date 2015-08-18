<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\LiveClientFactory;
use Topxia\WebBundle\Controller\BaseController;

/**
 * 作业互评控制器.
**/
class HomeworkPairReviewController extends BaseController
{
    /**
     * 随机显示一个作业答卷互评界面.
     * @param request
     * @param homeworkId , 作业id.
    **/
    public function createAction(Request $request, $homeworkId)
    {
        $homework = $this->getHomeworkService()->loadHomework($homeworkId);
        $course = $this -> getCourseService() -> loadCourse($homework['courseId']);
        $lesson = $this -> getCourseService() -> loadLesson($homework['lessonId']);

        $homeworkResult = $this -> getHomeworkService() -> randomizeHomeworkResultForPairReview($homework['id'], $this -> getCurrentUser() -> id);

        // $canCheckHomework = $this->getHomeworkService()->canCheckHomework($homeworkId);
        // if (!$canCheckHomework) {
        //     throw $this->createAccessDeniedException('无权批改作业！');
        // }
        // $course = $this->getCourseService()->getCourse($courseId);
        // $homework = $this->getHomeworkService()->getHomework($homeworkId);
        // if (empty($homework)) {
        //     throw $this->createNotFoundException();
        // }

        // $homeworkResult = $this->getHomeworkService()->getResultByHomeworkIdAndUserId($homeworkId, $userId);
        // if ($homeworkResult['status'] != 'reviewing') {
        //     return $this->createMessageResponse('warning', '作业已批阅或者未做完!');
        // }

        // if ($homework['courseId'] != $course['id']) {
        //     throw $this->createNotFoundException();
        // }

        // $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);
        
        // if (empty($lesson)) {
        //     return $this->createMessageResponse('info','作业所属课时不存在！');
        // }

        // if ($request->getMethod() == 'POST') {

        //     $checkHomeworkData = $request->request->all();
        //     $checkHomeworkData = empty($checkHomeworkData['data']) ? "" : $checkHomeworkData['data'];
        //     $this->getHomeworkService()->checkHomework($homeworkId,$userId,$checkHomeworkData);

        //     return $this->createJsonResponse(
        //         array(
        //             'courseId' => $courseId,
        //             'lessonId' => $homework['lessonId']
        //         )
        //     );
        // }

        // $itemSetResult = $this->getHomeworkService()->getItemSetResultByHomeworkIdAndUserId($homework['id'],$userId);
    
        // return $this->render('HomeworkBundle:CourseHomework:check.html.twig', array(
        //     'homework' => $homework,
        //     'itemSetResult' => $itemSetResult,
        //     'course' => $course,
        //     'lesson' => $lesson,
        //     'userId' => $userId,
        //     'questionStatus' => 'reviewing',
        //     'targetId' => $request->query->get('targetId'),
        //     'source' => $request->query->get('source','course'),
        //     'canCheckHomework' => $canCheckHomework
        // ));

        return $this->render("CustomWebBundle:HomeworkPairReview:create.html.twig", array(
            'homework' => $homework,
            'itemSet' => $homeworkResult['items'],
            'course' => $course,
            'lesson' => $lesson,
            'homeworkResult' => $homeworkResult,
            'questionStatus' => 'reviewing'
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