<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class HomeworkResult extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $homeworkId = $request->request->get('homeworkId');
        $answers = $request->request->all();
        $answers = !empty($data['answers']) ? $data['answers'] : array();
        $result = $this->getHomeworkService()->startHomework($homeworkId);
        $this->getHomeworkService()->submitHomework($result['id'], $answers);
        $res = array(
            'id' => $result['id'],
        );
        return $res;
    }

    public function get(Application $app, Request $request, $id)
    {
        $user = $this->getCurrentUser();
        $homeworkResult = $this->getHomeworkService()->getResult($id);
        $canLookHomeworkResult = $this->getHomeworkService()->canLookHomeworkResult($id);
        if (!$canLookHomeworkResult) {
            throw $this->createAccessDeniedException('无权查看作业！');
        }
        $itemSetResult = $this->getHomeworkService()->getItemSetResultByHomeworkIdAndUserId($homeworkResult['homeworkId'], $user['id'], $id);
        $homeworkResult = $this->getHomeworkService()->getResult($id);
        $res = array(
            'homeworkResult' => $homeworkResult,
            'itemSetResult' => $itemSetResult,
        );
        return $res;
    }

    public function filter(&$res)
    {
        return $res;
    }

    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    }
}
