<?php


namespace ApiBundle\Api\Resource\MultiClassProduct;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassProductService;

class MultiClassProduct extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $product = [
            'title' => $request->request->get('title'),
            'remark' => $request->request->get('remark'),
        ];

        if (empty($product['title'])){
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $existed = $this->getMultiClassProductService()->getProductByTitle($product['title']);

        if (!empty($existed['id'])) {
            throw MultiClassException::MULTI_CLASS_PRODUCT_EXIST();
        }

        $product = $this->getMultiClassProductService()->createProduct($product);

        return $product;
    }

    /**
     * @return MultiClassProductService
     */
    protected function getMultiClassProductService()
    {
        return $this->service('MultiClass:MultiClassProductService');
    }

}