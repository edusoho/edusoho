<?php

namespace AppBundle\Controller\SCRM;

use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Goods\Service\GoodsService;
use Biz\User\Dao\UserDao;
use ESCloud\SDK\Service\ScrmService;
use Symfony\Component\HttpFoundation\Request;

class BindController extends BaseController
{
    public function resultAction(Request $request, $uuid)
    {
        $assistantUuid = $request->query->get('assistantUuid');
//        $user = $this->getUserService()->getUserByUUID($uuid);
//        $this->getSCRMService()->setUserSCRMData($user);

        $assistant = empty($assistantUuid) ? [] : $this->getUserService()->getUserByScrmUuid($assistantUuid);

        $qrCodeUrl = $this->getSCRMService()->getAssistantQrCode($assistant);

        return $this->render('scrm/assistant-qrcode.html.twig', [
            'qrCodeUrl' => $qrCodeUrl,
            'assistant' => $assistant,
        ]);
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }

    /**
     * @return \Biz\SCRM\Service\SCRMService
     */
    protected function getSCRMService()
    {
        return $this->createService('SCRM:SCRMService');
    }

    /**
     * @return UserDao
     */
    protected function getUserDao()
    {
        return $this->getBiz()->dao('User:UserDao');
    }

    /**
     * @return ScrmService
     */
    protected function getScrmSdk()
    {
        $biz = $this->getBiz();

        return $biz['ESCloudSdk.scrm'];
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}
