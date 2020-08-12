<?php

namespace ApiBundle\Api\Resource\Good;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Favorite\Service\FavoriteService;
use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;
use Biz\System\Service\SettingService;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Class Good Good并不合适,商品真实本体是Goods,单复数同形,类名为Good是为了满足接口的定义规范（带有s结尾的单词比较难处理）
 */
class Good extends AbstractResource
{
    /**
     * @param $id
     *
     * @return \string[][]
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $id)
    {
        $user = $this->getCurrentUser();
        $goods = $this->getGoodsService()->getGoods($id);

        $this->getOCUtil()->single($goods, ['creator']);
        $product = $this->getProductService()->getProduct($goods['productId']);
        //获取状态中的组件
        $this->getOCUtil()->single($product, ['targetId'], 'course' == $product['targetType'] ? 'courseSet' : $product['targetType']);
        $goods['product'] = $product;
        $goods = $this->getGoodsService()->convertGoodsPrice($goods);

        $goods['specs'] = $this->getGoodsService()->findGoodsSpecsByGoodsId($goods['id']);
        $goodsEntity = $this->getGoodsService()->getGoodsEntityFactory()->create($goods['type']);
        foreach ($goods['specs'] as &$spec) {
            $spec = $this->getGoodsService()->convertSpecsPrice($goods, $spec);
            $spec['isMember'] = $goodsEntity->isSpecsMember($goods, $spec, $user['id']);
            $spec['isTeacher'] = $goodsEntity->isSpecsTeacher($goods, $spec, $user['id']);
            $spec['canMange'] = $goodsEntity->canManageTarget($goods);
            $spec['learnUrl'] = 'course' === $goods['type']
                ? $this->generateUrl('my_course_show', ['id' => $spec['targetId']], UrlGenerator::ABSOLUTE_URL)
                : $this->generateUrl('classroom_show', ['id' => $spec['targetId']], UrlGenerator::ABSOLUTE_URL);

            if ($this->isPluginInstalled('Vip')) {
                list($vipLevelInfo, $vipUser) = $goodsEntity->getVipInfo($goods, $spec, $user['id']);
                $spec['vipLevelInfo'] = $vipLevelInfo;
                $spec['vipUser'] = $vipUser;
            }
        }
        $goods['extensions'] = $this->collectGoodsExtensions($goods['product']);

        if ($this->getCurrentUser()->isLogin()) {
            $goods['isFavorite'] = !empty($this->getFavoriteService()->getUserFavorite($this->getCurrentUser()->getId(), 'goods', $goods['id']));
        }

        return $goods;
    }

    private function collectGoodsExtensions($product)
    {
        $defaultExtensions = [
            'teachers',
            'recommendGoods',
            'isFavorite',
        ];

        if ('classroom' === $product['targetType']) {
            $defaultExtensions = array_merge($defaultExtensions, ['classroomCourses']);
        }

        $goodsSetting = $this->getSettingService()->get('goods_setting', []);
        if (empty($goodsSetting['leading_join_enabled'])) {
            return $defaultExtensions;
        }

        return array_merge($defaultExtensions, ['mpQrCode']);
    }

    /**
     * @return GoodsService
     */
    private function getGoodsService()
    {
        return $this->service('Goods:GoodsService');
    }

    /**
     * @return ProductService
     */
    private function getProductService()
    {
        return $this->service('Product:ProductService');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    /**
     * @return FavoriteService
     */
    private function getFavoriteService()
    {
        return $this->service('Favorite:FavoriteService');
    }
}
