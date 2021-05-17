<?php

namespace ApiBundle\Api\Resource\Validation;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\MultiClass\Service\MultiClassProductService;
use Biz\MultiClass\Service\MultiClassService;

class ValidationTitle extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $type)
    {
        $title = $request->query->get('title', '');

        switch ($type) {
            case 'multiClass':
                $result = $this->getMultiClassService()->getMultiClassByTitle($title);
                break;
            case 'multiClassProduct':
                $result = $this->getMultiClassProductService()->getMultiClassProductByTitle($title);
                break;
            default:
                break;
        }

        return ['result' => empty($result) ? true : false];
    }

    /**
     * @return MultiClassService
     */
    private function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return MultiClassProductService
     */
    private function getMultiClassProductService()
    {
        return $this->service('MultiClass:MultiClassProductService');
    }
}
