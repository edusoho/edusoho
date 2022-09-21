<?php

namespace Biz\Goods\Service\Impl;

use ApiBundle\Api\Util\Money;
use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseSetService;
use Biz\Goods\Dao\GoodsDao;
use Biz\Goods\Dao\GoodsSpecsDao;
use Biz\Goods\GoodsEntityFactory;
use Biz\Goods\GoodsException;
use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use DiscountPlugin\Biz\Discount\Service\DiscountService;

class GoodsServiceImpl extends BaseService implements GoodsService
{
    public function getGoods($id)
    {
        return $this->getGoodsDao()->get($id);
    }

    public function createGoods($goods)
    {
        if (!ArrayToolkit::requireds($goods, ['productId', 'title', 'type'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $goods = ArrayToolkit::parts($goods, [
            'type',
            'productId',
            'title',
            'subtitle',
            'status',
            'summary',
            'orgId',
            'orgCode',
            'images',
            'creator',
            'minPrice',
            'maxPrice',
        ]);

        return $this->getGoodsDao()->create($goods);
    }

    public function publishGoods($id)
    {
        return $this->getGoodsDao()->update($id, ['status' => 'published', 'publishedTime' => time()]);
    }

    public function unpublishGoods($id)
    {
        return $this->getGoodsDao()->update($id, ['status' => 'unpublished', 'publishedTime' => time()]);
    }

    public function recommendGoods($id, $weight)
    {
        return $this->getGoodsDao()->update($id, ['recommendWeight' => $weight, 'recommendedTime' => time()]);
    }

    public function cancelRecommendGoods($id)
    {
        return $this->getGoodsDao()->update($id, ['recommendWeight' => 0, 'recommendedTime' => 0]);
    }

    public function updateGoods($id, $goods)
    {
        $goods = ArrayToolkit::parts($goods, [
            'type', //type不应该被更新，后面去掉
            'title',
            'images',
            'subtitle',
            'status',
            'summary',
            'orgId',
            'orgCode',
            'categoryId',
            'minPrice',
            'maxPrice',
            'maxRate',
            'ratingNum',
            'rating',
            'hitNum',
            'hotSeq',
            'recommendWeight',
            'recommendedTime',
            'discountId',
            'discountType',
        ]);

        return $this->getGoodsDao()->update($id, $goods);
    }

    public function updateGoodsMinAndMaxPrice($goodsId)
    {
        $specs = $this->findPublishedGoodsSpecsByGoodsId($goodsId);
        if (empty($specs)) {
            return $this->getGoodsDao()->update(
                $goodsId,
                ['minPrice' => 0.00, 'maxPrice' => 0.00]
            );
        }

        $prices = ArrayToolkit::column($specs, 'price');
        asort($prices);

        return $this->getGoodsDao()->update(
            $goodsId,
            ['minPrice' => current($prices), 'maxPrice' => end($prices)]
        );
    }

    public function freshGoodsSpecsCount($goodsId)
    {
        $goods = $this->getGoods($goodsId);
        if (empty($goods)) {
            return;
        }
        $publishedCount = $this->countGoodsSpecs(['goodsId' => $goodsId, 'status' => 'published']);
        $count = $this->countGoodsSpecs(['goodsId' => $goodsId]);
        $this->getGoodsDao()->update($goodsId, ['specsNum' => $count, 'publishedSpecsNum' => $publishedCount]);
    }

    public function deleteGoods($id)
    {
        return $this->getGoodsDao()->delete($id);
    }

    public function countGoods($conditions)
    {
        $conditions = $this->prepareGoodsFields($conditions);

        return $this->getGoodsDao()->count($conditions);
    }

    public function searchGoods($conditions, $orderBys, $start, $limit, $columns = [])
    {
        $conditions = $this->prepareGoodsFields($conditions);

        return $this->getGoodsDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    protected function prepareGoodsFields($conditions)
    {
        if (!empty($conditions['creatorName'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['creatorName']);
            $conditions['creator'] = $user ? $user['id'] : -1;
        }

        return $conditions;
    }

    /**
     * @param $productId
     *
     * @return mixed
     *               如果未来业务改造成 产品：商品 1：n 后，getGoodsByProductId就应该被舍弃，不再使用
     */
    public function getGoodsByProductId($productId)
    {
        return $this->getGoodsDao()->getByProductId($productId);
    }

    public function changeGoodsMaxRate($id, $maxRate)
    {
        $goods = $this->getGoods($id);
        $this->checkGoodsPermission($goods);

        return $this->getGoodsDao()->update($id, ['maxRate' => $maxRate]);
    }

    public function hitGoods($id)
    {
        $goods = $this->getGoods($id);

        if (empty($goods)) {
            $this->createNewException(GoodsException::GOODS_NOT_FOUND());
        }

        $goodsEntity = $this->getGoodsEntityFactory()->create($goods['type']);
        $hitNum = $goodsEntity->hitTarget($goods);
        if (empty($hitNum)) {
            return;
        }

        return $this->getGoodsDao()->update($goods['id'], ['hitNum' => $hitNum]);
    }

    public function hitGoodsSpecs($id)
    {
        $goodsSpecs = $this->getGoodsSpecs($id);
        $goods = $this->getGoods($goodsSpecs['goodsId']);
        if (empty($goods)) {
            $this->createNewException(GoodsException::GOODS_NOT_FOUND());
        }
        $goodsEntity = $this->getGoodsEntityFactory()->create($goods['type']);
        $goodsEntity->hitSpecs($goodsSpecs);
    }

    public function createGoodsSpecs($goodsSpecs)
    {
        if (!ArrayToolkit::requireds($goodsSpecs, [
            'goodsId',
            'targetId',
            'title',
        ])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $goodsSpecs = ArrayToolkit::parts($goodsSpecs, [
            'goodsId',
            'targetId',
            'status',
            'title',
            'images',
            'seq',
            'usageMode',
            'usageDays',
            'usageStartTime',
            'usageEndTime',
            'buyable',
            'buyableStartTime',
            'buyableEndTime',
        ]);
        $specs = $this->getGoodsSpecsDao()->create($goodsSpecs);
        $this->freshGoodsSpecsCount($specs['goodsId']);

        return $specs;
    }

    public function getGoodsSpecs($id)
    {
        return $this->getGoodsSpecsDao()->get($id);
    }

    public function updateGoodsSpecs($id, $goodsSpecs)
    {
        $goodsSpecs = ArrayToolkit::parts($goodsSpecs, [
            'title',
            'images',
            'price',
            'status',
            'seq',
            'coinPrice',
            'usageMode',
            'usageDays',
            'usageStartTime',
            'usageEndTime',
            'buyable',
            'buyableStartTime',
            'buyableEndTime',
            'maxJoinNum',
            'services',
        ]);
        $specs = $this->getGoodsSpecsDao()->update($id, $goodsSpecs);
        $this->updateGoodsMinAndMaxPrice($specs['goodsId']);

        return $specs;
    }

    public function changeGoodsSpecsPrice($specsId, $price)
    {
        $specs = $this->getGoodsSpecs($specsId);
        if (empty($specs)) {
            $this->createNewException(GoodsException::SPECS_NOT_FOUND());
        }
        $goods = $this->getGoods($specs['goodsId']);
        $this->checkGoodsPermission($goods);

        $specs = $this->getGoodsSpecsDao()->update($specsId, ['price' => $price]);
        $this->updateGoodsMinAndMaxPrice($goods['id']);

        return $specs;
    }

    public function publishGoodsSpecs($id)
    {
        $specs = $this->getGoodsSpecsDao()->update($id, ['status' => 'published']);
        $this->updateGoodsMinAndMaxPrice($specs['goodsId']);
        $this->freshGoodsSpecsCount($specs['goodsId']);

        return $specs;
    }

    public function unpublishGoodsSpecs($id)
    {
        $specs = $this->getGoodsSpecsDao()->update($id, ['status' => 'unpublished']);
        $this->updateGoodsMinAndMaxPrice($specs['goodsId']);
        $this->freshGoodsSpecsCount($specs['goodsId']);

        return $specs;
    }

    public function countGoodsSpecs($conditions)
    {
        return $this->getGoodsSpecsDao()->count($conditions);
    }

    public function searchGoodsSpecs($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getGoodsSpecsDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function deleteGoodsSpecs($id)
    {
        $specs = $this->getGoodsSpecs($id);
        $this->getGoodsSpecsDao()->delete($id);
        $this->freshGoodsSpecsCount($specs['goodsId']);
    }

    public function getGoodsSpecsByGoodsIdAndTargetId($goodsId, $targetId)
    {
        return $this->getGoodsSpecsDao()->getByGoodsIdAndTargetId($goodsId, $targetId);
    }

    public function findGoodsSpecsByGoodsId($goodsId)
    {
        return $this->getGoodsSpecsDao()->findByGoodsId($goodsId);
    }

    public function findPublishedGoodsSpecsByGoodsId($goodsId)
    {
        return $this->getGoodsSpecsDao()->findPublishedByGoodsId($goodsId);
    }

    public function getGoodsSpecsByProductIdAndTargetId($productId, $targetId)
    {
        $goods = $this->getGoodsByProductId($productId);
        if (empty($goods)) {
            $this->createNewException(GoodsException::GOODS_NOT_FOUND());
        }

        return $this->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $targetId);
    }

    public function findGoodsByIds($ids)
    {
        return ArrayToolkit::index($this->getGoodsDao()->findByIds($ids), 'id');
    }

    public function findGoodsByProductIds(array $productIds)
    {
        return $this->getGoodsDao()->findByProductIds($productIds);
    }

    public function findPublishedGoodsByProductIds(array $productIds)
    {
        return $this->getGoodsDao()->findPublishedByProductIds($productIds);
    }

    public function findGoodsSpecsByIds(array $ids)
    {
        return ArrayToolkit::index($this->getGoodsSpecsDao()->findByIds($ids), 'id');
    }

    public function convertGoodsPrice($goods)
    {
        $minDisplayPrice = $goods['minPrice'];
        $maxDisplayPrice = $goods['maxPrice'];
        if ($goods['discountId'] && $this->isPluginInstalled('Discount')) {
            $discount = $this->getDiscountService()->getDiscount($goods['discountId']);
            if($discount['endTime'] > time()) {
                if ('discount' === $discount['type']) {
                    $discountItem = $this->getDiscountService()->getItemByDiscountIdAndGoodsId($goods['discountId'], $goods['id']);
                    if (!empty($discountItem)) {
                        if ('discount' === $discount['discountType']) {
                            $minDisplayPrice = $goods['minPrice'] * $discountItem['discount'] / 10;
                            $maxDisplayPrice = $goods['maxPrice'] * $discountItem['discount'] / 10;
                        } else {
                            $minDisplayPrice = $goods['minPrice'] - $discountItem['reduce'];
                            $maxDisplayPrice = $goods['maxPrice'] - $discountItem['reduce'];
                        }
                        $goods['discount'] = $discount;
                    }
                } elseif ('free' === $discount['type']) {
                    $minDisplayPrice = '0.00';
                    $maxDisplayPrice = '0.00';
                    $goods['discount'] = $discount;
                } elseif ('global' === $discount['type']) {
                    $maxDisplayPrice = $goods['maxPrice'] * $discount['globalDiscount'] / 10;
                    $minDisplayPrice = $goods['minPrice'] * $discount['globalDiscount'] / 10;
                    $goods['discount'] = $discount;
                }
            }
        }
        $goods['maxPriceObj'] = Money::convert($goods['maxPrice']);
        $goods['minPriceObj'] = Money::convert($goods['minPrice']);
        $goods['minDisplayPrice'] = $minDisplayPrice;
        $goods['maxDisplayPrice'] = $maxDisplayPrice;
        $goods['minDisplayPriceObj'] = Money::convert($minDisplayPrice);
        $goods['maxDisplayPriceObj'] = Money::convert($maxDisplayPrice);

        return $goods;
    }

    public function convertSpecsPrice($goods, $specs)
    {
        $displayPrice = $specs['price'];
        if ($goods['discountId'] && $this->isPluginInstalled('Discount')) {
            $discount = $this->getDiscountService()->getDiscount($goods['discountId']);
            if($discount['endTime'] > time()) {
                if ('discount' === $discount['type']) {
                    $discountItem = $this->getDiscountService()->getItemByDiscountIdAndGoodsId($goods['discountId'], $goods['id']);
                    if (!empty($discount)) {
                        if ('discount' === $discount['discountType']) {
                            $displayPrice = $specs['price'] * $discountItem['discount'] / 10;
                        } else {
                            $displayPrice = $specs['price'] - $discountItem['reduce'];
                        }
                    }
                } elseif ('free' === $discount['type']) {
                    $displayPrice = '0.00';
                } elseif ('global' === $discount['type']) {
                    $displayPrice = $specs['price'] * $discount['globalDiscount'] / 10;
                }
            }
        }
        $specs['priceObj'] = Money::convert($specs['price']);
        $specs['displayPrice'] = $displayPrice;
        $specs['displayPriceObj'] = Money::convert($displayPrice);

        return $specs;
    }

    /**
     * @param $goods
     *
     * @return bool
     *              大于管理员的权限，教师权限且是当前商品的创建者,
     *              历史原因，如果满足实体（课程、班级等）的管理权限，也可以管理
     */
    public function canManageGoods($goods)
    {
        return $this->getCurrentUser()->isAdmin() || ($this->getCurrentUser()->isTeacher() && $this->isGoodsCreator($goods)) || $this->hasTargetManageRole($goods);
    }

    public function refreshGoodsHotSeq()
    {
        return $this->getGoodsDao()->refreshHotSeq();
    }

    protected function checkGoodsPermission($goods)
    {
        if (empty($goods)) {
            $this->createNewException(GoodsException::GOODS_NOT_FOUND());
        }
        if (!$this->canManageGoods($goods)) {
            $this->createNewException(GoodsException::FORBIDDEN_MANAGE_GOODS());
        }
    }

    protected function hasTargetManageRole($goods)
    {
        return $this->getGoodsEntityFactory()->create($goods['type'])->canManageTarget($goods);
    }

    /**
     * @param $goods
     *
     * @return bool
     *              创建者Id不为空，且创建者Id等于当前用户Id
     */
    protected function isGoodsCreator($goods)
    {
        return $goods['creator'] && (int) $goods['creator'] === (int) $this->getCurrentUser()->getId();
    }

    /**
     * @return GoodsEntityFactory
     */
    public function getGoodsEntityFactory()
    {
        return $this->biz['goods.entity.factory'];
    }

    /**
     * @return GoodsDao
     */
    protected function getGoodsDao()
    {
        return $this->createDao('Goods:GoodsDao');
    }

    /**
     * @return GoodsSpecsDao
     */
    protected function getGoodsSpecsDao()
    {
        return $this->createDao('Goods:GoodsSpecsDao');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->createService('Product:ProductService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return DiscountService
     */
    protected function getDiscountService()
    {
        return $this->createService('DiscountPlugin:Discount:DiscountService');
    }
}
