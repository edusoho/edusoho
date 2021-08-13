<?php

namespace AppBundle\Controller\SCRM;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\DeviceToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Goods\Service\GoodsService;
use Biz\SCRM\GoodsMediatorFactory;
use Biz\User\Dao\UserDao;
use ESCloud\SDK\Service\ScrmService;
use Symfony\Component\HttpFoundation\Request;

class BindController extends BaseController
{
    public function resultAction(Request $request, $uuid)
    {
        try {
            $result = $this->getScrmSdk()->getCustomer($uuid);
            $user = $this->getUserService()->getUserByUUID($uuid);
            $this->getUserService()->setUserScrmUuid($user['id'], $result['customerUniqueId']);
        } catch (\Exception $e) {
            return $this->createJsonResponse(['message' => $e->getMessage()]);
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
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

    /**
     * @return GoodsMediatorFactory
     */
    protected function getGoodsMediatorFactory()
    {
        $biz = $this->getBiz();

        return $biz['scrm_goods_mediator_factory'];
    }
}
