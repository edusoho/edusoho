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
    public function randomizeAction(Request $request, $homeworkId)
    {
        $userId=$this -> getCurrentUser() -> id;
        $homeworkResult = $this -> getHomeworkService() -> randomizeHomeworkResultForPairReview($homeworkId, $userId);

	$tip = "1.每位同学必须评价" . $homework['minReviews'] . "人，互评成绩按如下规则换算：未评分的=自己所得分数*" . ($homework['zeroPercent'] * 100) . "%，
        评价不到" . $homework['minReviews'] . "人的=自己所得分数*" . ($homework['partPercent'] * 100) . "%，
        达到" . $homework['minReviews'] . "人=自己所得分数*" . ($homework['completePercent'] * 100) . "%；";

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
                'questionStatus' => 'reviewing',
                'pairReviewCount' => $this->getHomeworkService() -> countUserHomeworkPairReviews($homeworkId, $userId)
            ));
        }
    }

    /**
     * 作业答卷互评提交界面.
     * @param request
     * @param homeworkResultId , 作业答卷id.
    **/
    public function createAction(Request $request, $homeworkResultId)
    {
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
        return $this->getServiceKernel()->createService('Custom:Homework.HomeworkService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Custom:Course.CourseService');
    }
}