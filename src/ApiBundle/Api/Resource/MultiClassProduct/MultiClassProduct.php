<?php


namespace ApiBundle\Api\Resource\MultiClassProduct;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassProductService;

class MultiClassProduct extends AbstractResource
{
    public function update(ApiRequest $request, $id)
    {
        $product = $this->getMultiClassProductService()->getProduct($id);

        if (empty($product)){
            throw MultiClassException::PRODUCT_NOT_FOUND();
        }

        $fields = [
            'title' => $request->request->get('title'),
            'remark' => $request->request->get('remark'),
        ];

        if (empty($fields['title'])){
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $existed = $this->getMultiClassProductService()->getProductByTitle($fields['title']);

        if (!empty($existed['id']) && $existed['title'] != $fields['title']) {
            throw MultiClassException::MULTI_CLASS_PRODUCT_EXIST();
        }

        return $this->getMultiClassProductService()->updateProduct($product['id'], $fields);
    }

    public function remove(ApiRequest $request, $id)
    {
        $product = $this->getMultiClassProductService()->getProduct($id);

        if (empty($product)){
            throw MultiClassException::PRODUCT_NOT_FOUND();
        }

        if ('default' === $product['type']){
            throw MultiClassException::CANNOT_DELETE_DEFAULT_PRODUCT();
        }

        $this->getMultiClassProductService()->deleteProduct($product['id']);

        return ['success' => true];
    }

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