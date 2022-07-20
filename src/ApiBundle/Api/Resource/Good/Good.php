<?php

namespace ApiBundle\Api\Resource\Good;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Favorite\Service\FavoriteService;
use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;
use Biz\System\Service\SettingService;
use Symfony\Component\Routing\Generator\UrlGenerator;
use VipPlugin\Biz\Vip\Service\VipService;

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
        $goods = $this->getGoodsService()->getGoods($id);
        $this->getOCUtil()->single($goods, ['creator']);

        $goods['product'] = $this->getProduct($goods);
        $goods = $this->getGoodsService()->convertGoodsPrice($goods);
        $goodsEntity = $this->getGoodsService()->getGoodsEntityFactory()->create($goods['type']);
        $goods['canManage'] = $goodsEntity->canManageTarget($goods);
        $goods['manageUrl'] = $goodsEntity->getManageUrl($goods);
        $goods['peopleShowNum'] = $this->getPeopleShowNum($goods);
        $goods['extensions'] = $this->collectGoodsExtensions($goods['product']);

        $this->fetchSpecs($goods, $goodsEntity, $request);

        if ($this->getCurrentUser()->isLogin()) {
            $goods['isFavorite'] = !empty($this->getFavoriteService()->getUserFavorite($this->getCurrentUser()->getId(), 'goods', $goods['id']));
        }

        $this->getGoodsService()->hitGoods($goods['id']);

        if ($request->query->get('targetId')) {
            $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $request->query->get('targetId'));
            $this->getGoodsService()->hitGoodsSpecs($goodsSpecs['id']);
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

    private function getProduct($goods)
    {
        $product = $this->getProductService()->getProduct($goods['productId']);
        //获取状态中的组件
        $this->getOCUtil()->single($product, ['targetId'], 'course' == $product['targetType'] ? 'courseSet' : $product['targetType']);

        return $product;
    }

    private function fetchSpecs(&$goods, $goodsEntity, $request)
    {
        $user = $this->getCurrentUser();
        if (1 == $request->query->get('preview')) {
            $goods['specs'] = $this->getGoodsService()->findGoodsSpecsByGoodsId($goods['id']);
        } else {
            $goods['specs'] = $this->getGoodsService()->findPublishedGoodsSpecsByGoodsId($goods['id']);
        }
        $goods['isMember'] = false;
        foreach ($goods['specs'] as &$spec) {
            if ('course' === $goods['type']) {
                $course = $this->getCourseService()->getCourse($spec['targetId']);
                $member = $this->getCourseMemberService()->getCourseMember($spec['targetId'], $user['id']);
                $spec['taskDisplay'] = $member ? 1 : $course['taskDisplay'];
            }
            $spec = $this->getGoodsService()->convertSpecsPrice($goods, $spec);
            $spec['isMember'] = $goodsEntity->isSpecsMember($goods, $spec, $user['id']);
            if ($spec['isMember']) {
                $goods['isMember'] = true;
            }
            $spec['isTeacher'] = $goodsEntity->isSpecsTeacher($goods, $spec, $user['id']);
            $spec['access'] = $goodsEntity->buySpecsAccess($goods, $spec);
            $spec['hasCertificate'] = $goodsEntity->hasCertificate($goods, $spec);
            $spec['learnUrl'] = 'course' === $goods['type']
                ? $this->generateUrl('my_course_show', ['id' => $spec['targetId']], UrlGenerator::ABSOLUTE_URL)
                : $this->generateUrl('classroom_show', ['id' => $spec['targetId']], UrlGenerator::ABSOLUTE_URL);

            $vipSetting = $this->getSettingService()->get('vip', []);
            if ($this->isPluginInstalled('Vip') && !empty($vipSetting['enabled'])) {
                list($vipLevelInfo, $vipUser) = $goodsEntity->getVipInfo($goods, $spec, $user['id']);
                $spec['vipLevelInfo'] = $vipLevelInfo;
                $spec['vipUser'] = $vipUser;
                $spec['canVipJoin'] = 'ok' == $this->getVipService()->checkUserVipRight($user['id'], $goods['type'], $spec['targetId']);
            }
            $spec['teacherIds'] = $goodsEntity->getSpecsTeacherIds($goods, $spec);
            $spec['services'] = $spec['services'] ?: [];
        }
        $this->getOCUtil()->multiple($goods['specs'], ['teacherIds']);
    }

    private function getPeopleShowNum($goods)
    {
        $goodSetting = $this->getSettingService()->get('goods_setting', []);
        if (!empty($goodSetting['show_number_data']) && 'visitor' === $goodSetting['show_number_data']) {
            return $goods['hitNum'];
        }

        return isset($goods['product']['target']['studentNum']) ? $goods['product']['target']['studentNum'] : 0;
    }

    /**
     * @return GoodsService
     */
    private function getGoodsService()
    {
        return $this->service('Goods:GoodsService');
    }

    /**
     * @return VipService
     */
    private function getVipService()
    {
        return $this->service('VipPlugin:Vip:VipService');
    }

    /**
     * @return ProductService
     */
    private function getProductService()
    {
        return $this->service('Product:ProductService');
    }

    /**
     * @return FavoriteService
     */
    private function getFavoriteService()
    {
        return $this->service('Favorite:FavoriteService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
