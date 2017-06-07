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
        if (!ArrayToolkit::requireds($fields, array('title', 'price', 'requireTelephone', 'requireEmail', 'requireAddress'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'title',
                'price',
                'about',
                'requireTelephone',
                'requireEmail',
                'requireAddress',
            )
        );
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
