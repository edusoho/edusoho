<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Goods\Service\GoodsService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\Product\Service\ProductService;
use Biz\Review\Service\ReviewService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class ReviewController extends BaseController
{
    //课程评价
    public function courseReviewListAction(Request $request)
    {
        $conditions = array_merge(['targetType' => 'course'], $request->query->all());

        $conditions = $this->prepareConditions($conditions);

        $paginator = new Paginator(
            $request,
            $this->getReviewService()->countCourseReviews($conditions),
            20
        );

        $reviews = $this->getReviewService()->searchCourseReviews(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseIds = [];
        $goodsIds = [];
        array_map(function ($review) use (&$courseIds, &$goodsIds) {
            if ('course' === $review['targetType']) {
                $courseIds[] = $review['targetId'];
            } else {
                $goodsIds[] = $review['targetId'];
            }
        }, $reviews);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $courses = empty($courseIds) ? [] : $this->getCourseService()->findCoursesByIds($courseIds);
        $courseSets = empty($courseIds) ? [] : $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($courses, 'courseSetId'));
        $goods = $this->getGoodsService()->findGoodsByIds($goodsIds);

        return $this->render('admin-v2/teach/review/course-review.html.twig', [
            'reviews' => $reviews,
            'users' => $users,
            'courses' => $courses,
            'courseSets' => $courseSets,
            'goods' => $goods,
            'paginator' => $paginator,
        ]);
    }

    public function deleteCourseReviewAction(Request $request, $id)
    {
        $this->getReviewService()->deleteReview($id);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteCourseReviewAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids as $id) {
            $this->getReviewService()->deleteReview($id);
        }

        return $this->createJsonResponse(true);
    }

    //班级评价
    public function classroomReviewListAction(Request $request)
    {
        $conditions = array_merge(['targetType' => 'classroom'], $request->query->all());

        $conditions = $this->prepareConditions($conditions);

        $paginator = new Paginator(
            $request,
            $this->getReviewService()->countClassroomReviews($conditions),
            20
        );

        $reviews = $this->getReviewService()->searchClassroomReviews(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $goods = $this->getGoodsService()->findGoodsByIds(ArrayToolkit::column($reviews, 'targetId'));

        return $this->render('admin-v2/teach/review/classroom-review.html.twig', [
            'reviews' => $reviews,
            'users' => $users,
            'goods' => $goods,
            'paginator' => $paginator,
        ]);
    }

    public function deleteClassroomReviewAction(Request $request, $id)
    {
        $this->getReviewService()->deleteReview($id);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteClassroomReviewAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids as $id) {
            $this->getReviewService()->deleteReview($id);
        }

        return $this->createJsonResponse(true);
    }

    //题库练习评价
    public function itemBankExerciseReviewListAction(Request $request)
    {
        $conditions = array_merge(['targetType' => 'item_bank_exercise'], $request->query->all());

        $conditions = $this->prepareConditions($conditions);

        $paginator = new Paginator(
            $request,
            $this->getReviewService()->countReviews($conditions),
            20
        );

        $reviews = $this->getReviewService()->searchReviews(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $exercises = $this->getItemBankExerciseService()->findByIds(ArrayToolkit::column($reviews, 'targetId'));

        return $this->render('admin-v2/teach/review/item-bank-exercise-review.html.twig', [
            'reviews' => $reviews,
            'users' => $users,
            'exercises' => $exercises,
            'paginator' => $paginator,
        ]);
    }

    protected function prepareConditions($conditions)
    {
        if (empty($conditions['rating'])) {
            unset($conditions['rating']);
        }

        if (!empty($conditions['courseTitle']) && 'course' == $conditions['targetType']) {
            unset($conditions['targetType']);
            unset($conditions['classroomTitle']);
        }

        if (!empty($conditions['classroomTitle']) && 'classroom' == $conditions['targetType']) {
            unset($conditions['targetType']);
        }

        if (!empty($conditions['exerciseTitle']) && 'item_bank_exercise' == $conditions['targetType']) {
            $exercises = $this->getItemBankExerciseService()->findExercisesByLikeTitle(trim($conditions['exerciseTitle']));
            $conditions['targetIds'] = ArrayToolkit::column($exercises, 'id');
            unset($conditions['exerciseTitle']);
            $conditions['targetIds'] = $conditions['targetIds'] ?: [-1];
        }

        if (!empty($conditions['author'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['author']);
            unset($conditions['author']);
            $conditions['userId'] = $user['id'] ? $user['id'] : -1;
        }

        $conditions['parentId'] = 0;

        return $conditions;
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->createService('Review:ReviewService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->createService('Product:ProductService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }
}
