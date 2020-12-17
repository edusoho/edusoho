<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;

class LatestCourseReviewsDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取最新发表的课程评论列表.
     *
     * 可传入的参数：
     *   courseId 可选 课程ID
     *   count 必需 课程话题数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 课程评论
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);

        $conditions = $this->checkCourseArguments($arguments);

        $defaultConditions = [
            'parentId' => 0,
            'targetType' => 'goods',
        ];

        if (isset($conditions['courseId'])) {
            $conditions['targetId'] = $conditions['courseId'];
            unset($conditions['courseId']);
            $conditions = array_merge($defaultConditions, $conditions);
        } else {
            $conditions = array_merge($defaultConditions, $conditions);
        }

        $courseReviews = $this->getReviewService()->searchReviews($conditions, ['createdTime' => 'DESC'], 0, $arguments['count']);

        return $this->getCoursesAndUsers($courseReviews);
    }

    protected function getCoursesAndUsers($courseRelations)
    {
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseRelations, 'userId'));
        $goodsIds = ArrayToolkit::column($courseRelations, 'targetId');
        $goods = ArrayToolkit::index($this->getGoodsService()->findGoodsByIds($goodsIds), 'id');
        $goodsSpecs = ArrayToolkit::group($this->getGoodsService()->findGoodsSpecsByGoodsIds($goodsIds), 'goodsId');
        array_walk($goodsSpecs, function (&$specs, $goodsId) use($goods, &$classroomIds, &$courseIds){
            $specs = $specs[0];
            $specs['targetType'] = $goods[$goodsId]['type'];
            $specs['targetTitle'] = $goods[$goodsId]['title'];
        });

        foreach ($courseRelations as &$courseRelation) {
            $userId = $courseRelation['userId'];
            $user = $users[$userId];
            unset($user['password']);
            unset($user['salt']);
            $courseRelation['User'] = $user;

            $courseRelation['target'] = $goodsSpecs[$courseRelation['targetId']];
        }

        return $courseRelations;
    }
}
