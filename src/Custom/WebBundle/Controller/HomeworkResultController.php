<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


use Topxia\WebBundle\Controller\BaseController;

/**
 * 作业答卷控制器.
 **/
class HomeworkResultController extends BaseController
{
    // /**
    //  * 保存作业答卷.
    //  * @param request
    //  * @param homeworkResultId , 作业答卷id.
    // **/
    // public function updateAction(Request $request, $homeworkResultId)
    // {
    //     if ($request->getMethod() == 'POST') {
    //         $homeworkResult = $this->getHomeworkService()->loadHomeworkResult($homeworkResultId);
    //         $homework = $this->getHomeworkService()->loadHomework($homeworkResult['homeworkId']);
    //         if ($homework['pairReview'] and intval($homework['completeTime']) < time()) {
    //             return $this->createMessageResponse('error',"已经超过作业提交截止时间，保存作业失败！");
    //         }

    //         $homeworkResult=$this->getHomeworkService()->updateHomeworkResultItems($homeworkResult, $request->request->all()['data']);

    //         return $this->createJsonResponse(array(
    //             'courseId'=>$homeworkResult['courseId'],
    //             'lessonId'=>$homeworkResult['lessonId']
    //         ));
    //     }

    //     return $this->createJsonResponse(true);
    // }

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