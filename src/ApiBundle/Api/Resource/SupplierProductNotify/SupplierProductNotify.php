<?php

namespace ApiBundle\Api\Resource\SupplierProductNotify;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\S2B2C\Service\CourseProductService;

class SupplierProductNotify extends AbstractResource
{
    private $notifyModels = [
        'newVersion' => 'setProductHasNewVersion',
        'statusChange' => 'refreshProductsStatus',
        'courseClosed' => 'supplierCourseClosed',
        'courseSetClosed' => 'supplierCourseSetClosed',
    ];

    /**
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     * @ApiConf(isRequiredAuth=true)
     */
    public function add(ApiRequest $request)
    {
        $params = $request->request->get('body');

        if (!ArrayToolkit::requireds($params, ['model'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        if (!in_array($params['model'], array_keys($this->notifyModels))) {
            throw CommonException::ERROR_PARAMETER();
        }

        $func = $this->notifyModels[$params['model']];

        return $this->getCourseProductService()->$func($params);
    }

    /**
     * @return CourseProductService
     */
    private function getCourseProductService()
    {
        return $this->service('S2B2C:CourseProductService');
    }
}
