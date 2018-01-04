<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Marketing\Service\MarketingService;

class ClassroomMarketingMember extends AbstractResource
{
    /**
     * @param ApiRequest $request
     * @param $classroomId
     *
     * @return array
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function add(ApiRequest $request, $classroomId)
    {
        $biz = $this->getBiz();
        $logger = $biz['logger'];
        $logger->debug('微营销通知处理订单');
        $postData = $request->request->all();

        try {
            return $this->getMarketingService()->addUserToClassroom($postData);
        } catch (\Exception $e) {
            $logger->error($e);

            return array('code' => 'error', 'msg' => 'ES处理微营销订单失败,'.$e->getTraceAsString());
        }
    }

    /**
     * @return MarketingService
     */
    protected function getMarketingService()
    {
        return $this->service('Marketing:MarketingService');
    }
}
