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
        $products = $this->getProductService()->findProductsByIds(ArrayToolkit::column($goods, 'productId'));
        $productsWithIndex = ArrayToolkit::index($products, 'id');
        $products = ArrayToolkit::group($products, 'targetType');
        $courseIds = isset($products['course']) ? ArrayToolkit::column($products['course'], 'targetId') : [];
        $classroomIds = isset($products['classroom']) ? ArrayToolkit::column($products['classroom'], 'targetId') : [];
        $courses = empty($courseIds) ? [] : $this->getCourseService()->findCoursesByIds($courseIds);
        $classrooms = empty($classroomIds) ? [] : $this->getClassroomService()->findClassroomsByIds($classroomIds);

        foreach ($courseRelations as &$courseRelation) {
            $userId = $courseRelation['userId'];
            $user = $users[$userId];
            unset($user['password']);
            unset($user['salt']);
            $courseRelation['User'] = $user;

            $goodsId = $courseRelation['targetId'];
            $productId = $goods[$goodsId]['productId'];
            $courseId = 'course' === $productsWithIndex[$productId]['targetType'] ? $productsWithIndex[$productId]['targetId'] : 0;
            $classroomId = 'classroom' === $productsWithIndex[$productId]['targetType'] ? $productsWithIndex[$productId]['targetId'] : 0;

            $courseRelation['targetType'] = $productsWithIndex[$productId]['targetType'];
            $courseRelation['course'] = $courseId ? $courses[$courseId] : [];
            $courseRelation['classroom'] = $classroomId ? $classrooms[$classroomId] : [];
        }

        return $courseRelations;
    }
}
