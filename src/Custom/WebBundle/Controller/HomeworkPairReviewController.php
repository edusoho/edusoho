<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * 作业互评控制器.
 **/
class HomeworkPairReviewController extends BaseController
{
    /**
     * 随机显示一个作业答卷互评界面
     * @param Request $request
     * @param $homeworkId
     * @return \Symfony\Component\HttpFoundation\Response|\Topxia\WebBundle\Controller\Response
     */
    public function randomizeAction(Request $request, $homeworkId)
    {
        $this->checkId($homeworkId);
        $userId=$this -> getCurrentUser() -> id;
        $homeworkResult = $this -> getHomeworkService() -> randomizeHomeworkResultForPairReview($homeworkId, $userId);

        if(empty($homeworkResult)){
            return $this->createMessageResponse('info', '没有可以互评的作业!');
        }else{
            return $this->render("CustomWebBundle:HomeworkPairReview:create.html.twig", array(
                'homeworkResult' => $homeworkResult,
                'itemSet' => $homeworkResult['items'],
                'homeworkId' => $homeworkResult['homeworkId'],
                'homework' => $homeworkResult['homework'],
                'course' => $homeworkResult['course'],
                'lesson' => $homeworkResult['lesson'],
                'pairReviewCount' => $this->getHomeworkService() -> countUserHomeworkPairReviews($homeworkId, $userId),
                'view' => 'reviewing'
            ));
        }
    }

    /**
     * @param Request $request
     * @param $homeworkResultId
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction(Request $request, $homeworkResultId)
    {
        $this->checkId($homeworkResultId);
        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $fields = empty($fields['data']) ? "" : $fields['data'];
            $homeworkReview=$this->getHomeworkService()->createHomeworkPairReview($homeworkResultId,$this->getCurrentUser()->id,$fields);
            
            return $this->createJsonResponse(
                array(
                    'courseId' => $homeworkReview['homeworkResult']['courseId'],
                    'lessonId' => $homeworkReview['homeworkResult']['lessonId']
                )
            );
        }

        return $this->createJsonResponse(true);
    }

    /**
     * 获取作业服务.
     **/
    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework.HomeworkService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Custom:Course.CourseService');
    }
}