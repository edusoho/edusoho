<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use Biz\Goods\Service\GoodsService;
use Biz\Review\Service\ReviewService;
use Biz\User\Service\UserService;

class LatestGoodsReviewsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取最新发表的商品评论列表.
     *
     * 可传入的参数：
     *   count 必需 商品评价显示数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 商品评论
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['count'])) {
            $arguments['count'] = 4;
        }

        $conditions = [
            'parentId' => 0,
            'targetType' => 'goods',
            'notAuditStatus' => ['illegal', 'none_checked'],
        ];

        $reviews = $this->getReviewService()->searchReviews($conditions, ['createdTime' => 'DESC'], 0, $arguments['count']);

        return $this->getGoodsAndUsers($reviews);
    }

    protected function getGoodsAndUsers($goodsRelations)
    {
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($goodsRelations, 'userId'));
        $goodsIds = ArrayToolkit::column($goodsRelations, 'targetId');
        $goods = ArrayToolkit::index($this->getGoodsService()->findGoodsByIds($goodsIds), 'id');

        foreach ($goodsRelations as &$goodsRelation) {
            $userId = $goodsRelation['userId'];
            $user = $users[$userId];
            unset($user['password']);
            unset($user['salt']);
            $goodsRelation['User'] = $user;

            $goodsId = $goodsRelation['targetId'];
            $goodsRelation['goods'] = isset($goods[$goodsId]) ? $goods[$goodsId] : [];
        }

        return $goodsRelations;
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->getServiceKernel()->getBiz()->service('Review:ReviewService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getServiceKernel()->getBiz()->service('User:UserService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->getServiceKernel()->getBiz()->service('Goods:GoodsService');
    }
}
