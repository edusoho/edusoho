<?php

namespace Biz\Review\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Goods\Service\GoodsService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\Review\Dao\ReviewDao;
use Biz\Review\ReviewException;
use Biz\Review\Service\ReviewService;
use Biz\Sensitive\Service\SensitiveService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;

class ReviewServiceImpl extends BaseService implements ReviewService
{
    const RATING_LIMIT = 5;

    //    TODO: 剥离完课程与班级数据后删除
    protected $reviewMap = [
        'course' => 'tryCreateCourseReview',
        'classroom' => 'tryCreateClassroomReview',
        'item_bank_exercise' => 'tryCreateItemBankExerciseReview',
        'goods' => 'tryCreateGoodsReview',
    ];

    public function getReview($id)
    {
        return $this->getReviewDao()->get($id);
    }

    //    TODO: 暂时兼容课程、班级,权限判断修改
    public function tryCreateReview($review)
    {
        if (!in_array($review['targetType'], array_keys($this->reviewMap))) {
            return $review;
        }

        $function = $this->reviewMap[$review['targetType']];

        return $this->$function($review);
    }

    public function createReview($review)
    {
        if (!ArrayToolkit::requireds($review, ['userId', 'rating', 'targetType', 'targetId', 'content'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if ($review['rating'] > self::RATING_LIMIT) {
            $this->createNewException(ReviewException::RATING_LIMIT());
        }

        $review = $this->tryCreateReview($review);

        $review = ArrayToolkit::parts($review, [
            'userId', 'targetType', 'targetId', 'content', 'rating', 'parentId',
        ]);

        $review['content'] = $this->purifyHtml($review['content']);
        $sensitiveResult = $this->getSensitiveService()->sensitiveCheckResult($review['content'], 'review');
        $review['content'] = $sensitiveResult['content'];

        $review = $this->getReviewDao()->create($review);
        $this->dispatchEvent('review.create', new Event($review, ['sensitiveResult' => $sensitiveResult]));

        return $review;
    }

    public function getReviewByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId)
    {
        return $this->getReviewDao()->getByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);
    }

    public function updateReview($id, $review)
    {
        $existed = $this->getReviewDao()->get($id);
        $this->tryOperateReview($existed);
        $review = ArrayToolkit::parts($review, ['content', 'rating']);

        $review['content'] = $this->purifyHtml($review['content']);
        $sensitiveResult = $this->getSensitiveService()->sensitiveCheckResult($review['content'], 'review');
        $review['content'] = $sensitiveResult['content'];

        $review = $this->getReviewDao()->update($id, $review);

        $this->dispatchEvent('review.update', new Event($review, ['sensitiveResult' => $sensitiveResult]));

        return $review;
    }

    public function deleteReview($id)
    {
        $review = $this->getReviewDao()->get($id);

        if (empty($review)) {
            return true;
        }

        $this->tryOperateReview($review);
        $this->getReviewDao()->delete($id);

        $this->getReviewDao()->deleteByParentId($review['id']);

        $this->dispatchEvent('review.delete', new Event($review));

        return true;
    }

    public function deleteReviewsByUserId($userId)
    {
        return $this->getReviewDao()->deleteByUserId($userId);
    }

    public function deleteReviewsByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->getReviewDao()->deleteByTargetTypeAndTargetId($targetType, $targetId);
    }

    public function countReviews($conditions)
    {
        return $this->getReviewDao()->count($conditions);
    }

    public function countCourseReviews($conditions)
    {
        return $this->getReviewDao()->countCourseReviews($conditions);
    }

    public function searchCourseReviews($conditions, $orderBys, $start, $limit)
    {
        return $this->getReviewDao()->searchCourseReviews($conditions, $orderBys, $start, $limit);
    }

    public function countClassroomReviews($conditions)
    {
        return $this->getReviewDao()->countClassroomReviews($conditions);
    }

    public function searchClassroomReviews($conditions, $orderBys, $start, $limit)
    {
        return $this->getReviewDao()->searchClassroomReviews($conditions, $orderBys, $start, $limit);
    }

    public function searchReviews($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getReviewDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function countRatingByTargetTypeAndTargetId($targetType, $targetId)
    {
        $conditions = [
            'targetType' => $targetType,
            'targetId' => $targetId,
            'parentId' => 0,
        ];
        $ratingNum = $this->countReviews($conditions);
        $rating = $this->getReviewDao()->sumRatingByConditions($conditions);

        return [
            'ratingNum' => $ratingNum,
            'rating' => $ratingNum ? $rating / $ratingNum : 0,
        ];
    }

    public function countRatingByTargetTypeAndTargetIds($targetType, $targetIds)
    {
        $conditions = [
            'targetType' => $targetType,
            'targetIds' => $targetIds,
            'parentId' => 0,
        ];
        $ratingNum = $this->countReviews($conditions);
        $rating = $this->getReviewDao()->sumRatingByConditions($conditions);

        return [
            'ratingNum' => $ratingNum,
            'rating' => $ratingNum ? $rating / $ratingNum : 0,
        ];
    }

    public function canReviewBySelf($reportId, $userId)
    {
        //判断当前批阅是不是题库练习或考试练习
        $answerReport = $this->getAnswerReportService()->getSimple($reportId);
        if (empty($answerReport)) {
            return false;
        }
        // 查询场次是否在activity_homework
        $activityHomework = $this->getHomeworkActivityService()->getByAnswerSceneId($answerReport['answer_scene_id']);
        if (!empty($activityHomework)) {
            return false;
        }
        // 查询场次是否在activity_testpaper
        $activityTestpaper = $this->getTestpaperActivityService()->getActivityByAnswerSceneId($answerReport['answer_scene_id']);
        if (!empty($activityTestpaper)) {
            return false;
        }
        // 只能批阅自己
        if ($answerReport['user_id'] != $userId) {
            return false;
        }

        return true;
    }

    protected function tryOperateReview($review)
    {
        if ($review['userId'] != $this->getCurrentUser()->getId() && !$this->getCurrentUser()->isAdmin()) {
            $this->createNewException(ReviewException::FORBIDDEN_OPERATE_REVIEW());
        }
    }

    //    TODO: 商品剥离暂时兼容课程
    protected function tryCreateCourseReview($review)
    {
        if (!$this->getCourseService()->canTakeCourse($review['targetId'])) {
            $this->createNewException(ReviewException::FORBIDDEN_CREATE_REVIEW());
        }

        return $review;
    }

    protected function tryCreateGoodsReview($review)
    {
        $goods = $this->getGoodsService()->getGoods($review['targetId']);
        if (empty($goods)) {
            throw ReviewException::NOT_FOUND_REVIEW();
        }
        $specs = $this->getGoodsService()->findGoodsSpecsByGoodsId($review['targetId']);
        if (empty($specs)) {
            throw ReviewException::NOT_FOUND_REVIEW();
        }

        switch ($goods['type']) {
            case 'classroom':
                $this->canTakeClassRoomReview($specs);
                break;
            case 'course':
            default:
                $this->canTakeCourseReview($specs);
                break;
        }

        return $review;
    }

    /**
     * 是否能够评价课程 对于课程ID是通过goods_specs表的targetId获取的
     */
    private function canTakeCourseReview($specs)
    {
        $courseIds = array_column($specs, 'targetId');
        $canTakeCourse = false;
        foreach ($courseIds as $courseId) {
            if ($this->getCourseService()->canTakeCourse($courseId)) {
                $canTakeCourse = true;
                break;
            }
        }

        if (!$canTakeCourse) {
            throw ReviewException::FORBIDDEN_CREATE_REVIEW();
        }
    }

    /**
     * 是否能够评价班級 对于班级ID是通过goods_specs表的targetId获取的
     */
    private function canTakeClassRoomReview($specs)
    {
        //班级有且仅有一个
        if ($this->getClassroomService()->canTakeClassroom($specs[0]['targetId'])) {
            return;
        }

        $this->createNewException(ReviewException::FORBIDDEN_CREATE_REVIEW());
    }

    //    TODO: 商品剥离暂时兼容班级
    protected function tryCreateClassroomReview($review)
    {
        if (!$this->getClassroomService()->canTakeClassroom($review['targetId'])) {
            $this->createNewException(ReviewException::FORBIDDEN_CREATE_REVIEW());
        }

        return $review;
    }

    //    TODO: 商品剥离暂时兼容题库练习
    protected function tryCreateItemBankExerciseReview($review)
    {
        if (!$this->getItemBankExerciseService()->canTakeItemBankExercise($review['targetId'])) {
            $this->createNewException(ReviewException::FORBIDDEN_CREATE_REVIEW());
        }

        return $review;
    }

    /**
     * @return ReviewDao
     */
    protected function getReviewDao()
    {
        return $this->createDao('Review:ReviewDao');
    }

    /**
     * @return SensitiveService
     */
    protected function getSensitiveService()
    {
        return $this->createService('Sensitive:SensitiveService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->createService('Activity:HomeworkActivityService');
    }

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->createService('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }
}
