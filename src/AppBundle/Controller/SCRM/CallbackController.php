<?php

namespace AppBundle\Controller\SCRM;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;
use AppBundle\Controller\BaseController;
use Biz\Goods\Service\GoodsService;
use ESCloud\SDK\Service\ScrmService;
use Symfony\Component\HttpFoundation\Request;

class CallbackController extends BaseController
{
    public function goodsAction(Request $request)
    {
        $query = $request->query->all();
        $this->filterQuery($query);

        $userInfo = $this->getScrmSdk()->getUserByToken($query['user_token']);
        var_dump($userInfo);
        exit();
        if (!empty($userInfo)) {
            $this->getUserService()->register([
                'userToken',
                'nickname',
                'email',
            ]);
        }

        return $this->createJsonResponse(true);
    }

    private function filterQuery($query)
    {
        if (ArrayToolkit::requireds($query, [
            'user_token',
            'receipt_token',
        ])) {
            throw new InvalidArgumentException('参数不正确！');
        }
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }

    /**
     * @return ScrmService
     */
    protected function getScrmSdk()
    {
        $biz = $this->getBiz();

        return $biz['ESCloudSdk.scrm'];
    }
}
