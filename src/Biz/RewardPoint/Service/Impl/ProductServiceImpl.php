<?php

namespace Biz\RewardPoint\Service\Impl;

use Biz\BaseService;
use Biz\RewardPoint\Service\ProductService;
use AppBundle\Common\ArrayToolkit;

class ProductServiceImpl extends BaseService implements ProductService
{
    public function createProduct($fields)
    {
        $this->validateFields($fields);

        $fields = $this->filterFields($fields);

        $rewardPointProduct = $this->getRewardPointProductDao()->create($fields);

        $this->getLogService()->info('RewardPoint', 'create', "Add RewardPoint Product{$rewardPointProduct['title']}(#{$rewardPointProduct['id']})");

        return $rewardPointProduct;
    }

    public function updateProduct($id, array $fields)
    {
        $this->checkProductExist($id);

        $this->validateFields($fields);

        $fields = $this->filterFields($fields);

        $updatedRewardPointProduct = $this->getRewardPointProductDao()->update($id, $fields);

        $this->getLogService()->info('RewardPoint', 'update', "Update RewardPoint Product{$updatedRewardPointProduct['title']}(#{$id})");

        return $updatedRewardPointProduct;
    }

    public function upShelves($id)
    {
        $this->checkProductExist($id);

        return $this->getRewardPointProductDao()->update($id, array('status' => 'published'));
    }

    public function downShelves($id)
    {
        $this->checkProductExist($id);

        return $this->getRewardPointProductDao()->update($id, array('status' => 'draft'));
    }

    public function changeProductCover($id, $coverArray)
    {
        if (empty($coverArray)) {
            throw $this->createInvalidArgumentException('Invalid Param: cover');
        }
        $product = $this->getProduct($id);
        $covers = array();
        foreach ($coverArray as $cover) {
            $file = $this->getFileService()->getFile($cover['id']);
            $covers[$cover['type']] = $file['uri'];
        }

        $product = $this->getRewardPointProductDao()->update($product['id'], array('cover' => $covers));

        $this->getLogService()->info(
            'product',
            'update_cover',
            "更新课程《{$product['title']}》(#{$product['id']})图片",
            $covers
        );

        return $product;
    }

    public function deleteProduct($id)
    {
        $this->checkProductExist($id);

        return $this->getRewardPointProductDao()->delete($id);
    }

    public function getProduct($id)
    {
        return $this->getRewardPointProductDao()->get($id);
    }

    public function findProductsByIds(array $ids)
    {
        return $this->getRewardPointProductDao()->findByIds($ids);
    }

    public function countProducts($conditions)
    {
        return $this->getRewardPointProductDao()->count($conditions);
    }

    public function searchProducts($conditions, $orderBy, $start, $limit)
    {
        return $this->getRewardPointProductDao()->search($conditions, $orderBy, $start, $limit);
    }

    protected function checkProductExist($id)
    {
        $product = $this->getProduct($id);

        if (empty($product)) {
            throw $this->createNotFoundException("RewardPoint Product {$id} Not Exist, Operation Failed!");
        }

        return $product;
    }

    protected function validateFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('title', 'img', 'price', 'about'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function filterFields($fields)
    {
        $filterFields = ArrayToolkit::parts(
            $fields,
            array(
                'title',
                'price',
                'img',
                'about',
                'requireConsignee',
                'requireTelephone',
                'requireEmail',
                'requireAddress',
            )
        );

        $filterFields['requireConsignee'] = empty($filterFields['requireConsignee']) ? 0 : $filterFields['requireConsignee'];
        $filterFields['requireTelephone'] = empty($filterFields['requireTelephone']) ? 0 : $filterFields['requireTelephone'];
        $filterFields['requireEmail'] = empty($filterFields['requireEmail']) ? 0 : $filterFields['requireEmail'];
        $filterFields['requireAddress'] = empty($filterFields['requireAddress']) ? 0 : $filterFields['requireAddress'];

        return $filterFields;
    }

    /**
     * @return RewardPointProductDao
     */
    protected function getRewardPointProductDao()
    {
        return $this->createDao('RewardPoint:ProductDao');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
