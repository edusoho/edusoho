<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;

class TestpaperController extends BaseController
{

    public function userResultJsonAction(Request $request, $id)
    {
        $user = $this->getCurrentUser()->id;
        if (empty($user)) {
            return $this->createJsonResponse(array('error' => '您尚未登录系统或登录已超时，请先登录。'));
        }

        $testPaper = $this->getTestpaperService()->getTestPaper($id);
        if (empty($testPaper)) {
            return $this->createJsonResponse(array('error' => '试卷已删除，请联系管理员。'));
        }

        $testResult = $this->getTestpaperService()->findTestPaperResultByTestIdAndUserId($id, $user['id']);
        if (empty($testResult)) {
            return $this->createJsonResponse(array('status' => 'nodo'));
        }

        return $this->createJsonResponse(array('status' => $testResult['status']));
    }

    private function getTestpaperService()
    {
        return $this -> getServiceKernel()->createService('Quiz.TestService');
    }

}