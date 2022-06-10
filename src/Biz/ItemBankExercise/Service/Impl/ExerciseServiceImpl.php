<?php

namespace Biz\ItemBankExercise\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Accessor\AccessorInterface;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Content\Service\FileService;
use Biz\Exception\UnableJoinException;
use Biz\ItemBankExercise\Dao\AssessmentExerciseDao;
use Biz\ItemBankExercise\Dao\AssessmentExerciseRecordDao;
use Biz\ItemBankExercise\Dao\ChapterExerciseRecordDao;
use Biz\ItemBankExercise\Dao\ExerciseDao;
use Biz\ItemBankExercise\Dao\ExerciseMemberDao;
use Biz\ItemBankExercise\Dao\ExerciseModuleDao;
use Biz\ItemBankExercise\Dao\ExerciseQuestionRecordDao;
use Biz\ItemBankExercise\Dao\MemberOperationRecordDao;
use Biz\ItemBankExercise\ExpiryMode\ExpiryModeFactory;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\OperateReason;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\System\Service\LogService;
use Biz\User\UserException;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\ProductMallGoodsRelationService;

class ExerciseServiceImpl extends BaseService implements ExerciseService
{
    public function update($id, $fields)
    {
        return $this->getExerciseDao()->update($id, $fields);
    }

    public function create($exercise)
    {
        if (!ArrayToolkit::requireds($exercise, ['questionBankId'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if ($this->getByQuestionBankId($exercise['questionBankId'])) {
            $this->createNewException(ItemBankExerciseException::EXERCISE_EXISTS());
        }

        try {
            $this->beginTransaction();

            $exercise['creator'] = $this->getCurrentUser()->getId();
            $exercise = $this->getExerciseDao()->create($exercise);
            $this->getExerciseMemberService()->addTeacher($exercise['id']);
            $this->createChapterModule($exercise);
            $this->getExerciseModuleService()->createAssessmentModule($exercise['id'], '模拟考试');

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $exercise;
    }

    protected function createChapterModule($exercise)
    {
        $scene = $this->getAnswerSceneService()->create(
            [
                'name' => '章节练习',
                'limited_time' => 0,
                'do_times' => 0,
                'redo_interval' => 0,
                'need_score' => 0,
                'enable_facein' => 0,
                'pass_score' => 0,
                'manual_marking' => 1,
                'start_time' => 0,
                'doing_look_analysis' => 1,
            ]
        );
        $this->getItemBankExerciseModuleDao()->create([
            'exerciseId' => $exercise['id'],
            'title' => '章节练习',
            'type' => 'chapter',
            'answerSceneId' => $scene['id'],
        ]);
    }

    public function get($exerciseId)
    {
        return $this->getExerciseDao()->get($exerciseId);
    }

    public function count($conditions)
    {
        return $this->getExerciseDao()->count($conditions);
    }

    public function findByIds($ids)
    {
        $itemBankExercises = $this->getExerciseDao()->findByIds($ids);

        return ArrayToolkit::index($itemBankExercises, 'id');
    }

    public function search($conditions, $orderBy, $start, $limit, $columns = [])
    {
        $orderBy = $this->getOrderBys($orderBy);
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getExerciseDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    protected function getOrderBys($order)
    {
        if (is_array($order)) {
            return $order;
        }

        $typeOrderByMap = [
            'rating' => ['rating' => 'DESC'],
            'recommended' => ['recommendedTime' => 'DESC'],
            'studentNum' => ['studentNum' => 'DESC'],
            'recommendedSeq' => ['recommendedSeq' => 'ASC', 'recommendedTime' => 'DESC'],
            'hotSeq' => ['studentNum' => 'DESC', 'id' => 'DESC'],
        ];
        if (isset($typeOrderByMap[$order])) {
            return $typeOrderByMap[$order];
        } else {
            return ['createdTime' => 'DESC'];
        }
    }

    public function tryManageExercise($exerciseId = 0, $teacher = 1)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $exercise = $this->getExerciseDao()->get($exerciseId);

        if (empty($exercise)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE());
        }

        if (!$this->hasExerciseManagerRole($exerciseId, $teacher)) {
            $this->createNewException(ItemBankExerciseException::FORBIDDEN_MANAGE_EXERCISE());
        }

        return $exercise;
    }

    public function hasExerciseManagerRole($exerciseId = 0, $teacher = 1)
    {
        $user = $this->getCurrentUser();
        //未登录，无权限管理
        if (!$user->isLogin()) {
            return false;
        }

        //不是管理员，无权限管理
        if ($this->hasAdminRole()) {
            return true;
        }

        $exercise = $this->get($exerciseId);
        //课程不存在，无权限管理
        if (empty($exercise)) {
            return false;
        }

        if (0 == $teacher || in_array($user->getId(), $exercise['teacherIds'])) {
            return true;
        }

        return false;
    }

    public function updateExerciseStatistics($id, $fields)
    {
        if (empty($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $updateFields = [];
        foreach ($fields as $field) {
            if ('studentNum' === $field) {
                $updateFields['studentNum'] = $this->countStudentsByExerciseId($id);
            }
        }

        if (empty($updateFields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $exercise = $this->getExerciseDao()->update($id, $updateFields);

        return $exercise;
    }

    public function countStudentsByExerciseId($exerciseId)
    {
        return $this->getExerciseMemberDao()->count(
            [
                'exerciseId' => $exerciseId,
                'role' => 'student',
            ]
        );
    }

    public function isExerciseTeacher($exerciseId, $userId)
    {
        $member = $this->getExerciseDao()->get($exerciseId);

        return !empty($member) && in_array($userId, $member['teacherIds']);
    }

    public function changeExerciseCover($id, $coverArray)
    {
        if (empty($coverArray)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }
        $exercise = $this->tryManageExercise($id);
        $covers = [];
        foreach ($coverArray as $cover) {
            $file = $this->getFileService()->getFile($cover['id']);
            $covers[$cover['type']] = $file['uri'];
        }

        $exercise = $this->getExerciseDao()->update($exercise['id'], ['cover' => $covers]);

        return $exercise;
    }

    public function getByQuestionBankId($questionBankId)
    {
        return $this->getExerciseDao()->getByQuestionBankId($questionBankId);
    }

    public function findByQuestionBankIds($questionBankIds)
    {
        $itemBankExercises = $this->getExerciseDao()->findByQuestionBankIds($questionBankIds);

        return ArrayToolkit::index($itemBankExercises, 'id');
    }

    public function searchOrderByStudentNumAndLastDays($conditions, $lastDays, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getExerciseDao()->searchOrderByStudentNumAndLastDays($conditions, $lastDays, $start, $limit);
    }

    public function searchOrderByRatingAndLastDays($conditions, $lastDays, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getExerciseDao()->searchOrderByRatingAndLastDays($conditions, $lastDays, $start, $limit);
    }

    public function updateModuleEnable($exercised, $enable)
    {
        return $this->getExerciseDao()->update($exercised, $enable);
    }

    public function updateBaseInfo($id, $fields)
    {
        $exercise = $this->tryManageExercise($id);

        $fields = $this->validateExpiryMode($fields);
        $fields = $this->processFields($exercise, $fields);
        $exercise = $this->getExerciseDao()->update($id, $fields);

        return $exercise;
    }

    public function deleteExercise($exerciseId)
    {
        $this->tryManageExercise($exerciseId);

        try {
            $this->beginTransaction();

            $this->getExerciseDao()->delete($exerciseId);

            $this->getExerciseMemberDao()->deleteByExerciseId($exerciseId);

            $this->getMemberOperationRecordDao()->deleteByExerciseId($exerciseId);

            $this->getItemBankExerciseModuleDao()->deleteByExerciseId($exerciseId);

            $this->getAssessmentExerciseDao()->deleteByExerciseId($exerciseId);

            $this->getAssessmentExerciseRecordDao()->deleteByExerciseId($exerciseId);

            $this->getChapterExerciseRecordDao()->deleteByExerciseId($exerciseId);

            $this->getExerciseQuestionRecordDao()->deleteByExerciseId($exerciseId);

            if ($this->getProductMallGoodsRelationService()->checkEsProductCanDelete([$exerciseId],'questionBank') === 'error') {
                throw $this->createServiceException('该产品已在营销商城中上架售卖，请将对应商品下架后再进行删除操作');
            }

            $this->dispatchEvent('questionBankProduct.delete',new Event(['id'=>$exerciseId]));

            $user = $this->getCurrentUser();
            $this->getLogService()->info('item_bank_exercise', 'delete_exercise', "删除练习{$user['nickname']}(#{$user['id']})");

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function recommendExercise($exerciseId, $number)
    {
        $this->tryManageExercise($exerciseId);
        if (!is_numeric($number)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $fields = [
            'recommended' => 1,
            'recommendedSeq' => (int) $number,
            'recommendedTime' => time(),
        ];

        $exercise = $this->getExerciseDao()->update($exerciseId, $fields);

        $user = $this->getCurrentUser();
        $this->getLogService()->info('item_bank_exercise', 'recommend_exercise', "推荐练习{$user['nickname']}(#{$user['id']})");

        return $exercise;
    }

    public function cancelRecommendExercise($exerciseId)
    {
        $this->tryManageExercise($exerciseId);
        $fields = [
            'recommended' => 0,
            'recommendedTime' => 0,
            'recommendedSeq' => 0,
        ];

        $exercise = $this->getExerciseDao()->update($exerciseId, $fields);

        $user = $this->getCurrentUser();
        $this->getLogService()->info('item_bank_exercise', 'cancel_recommend_exercise', "取消推荐练习{$user['nickname']}(#{$user['id']})");

        return $exercise;
    }

    public function publishExercise($exerciseId)
    {
        $this->tryManageExercise($exerciseId);

        $exercise = $this->getExerciseDao()->update($exerciseId, ['status' => 'published']);

        $user = $this->getCurrentUser();
        $this->getLogService()->info('item_bank_exercise', 'publish_exercise', "发布练习{$user['nickname']}(#{$user['id']})");

        return $exercise;
    }

    public function closeExercise($exerciseId)
    {
        $this->tryManageExercise($exerciseId);

        $exercise = $this->getExerciseDao()->update($exerciseId, ['status' => 'closed']);

        $user = $this->getCurrentUser();
        $this->getLogService()->info('item_bank_exercise', 'close_exercise', "关闭练习{$user['nickname']}(#{$user['id']})");

        return $exercise;
    }

    public function canTakeItemBankExercise($exerciseId)
    {
        $exercise = $this->get($exerciseId);

        if (empty($exercise)) {
            return false;
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }

        $member = $this->getExerciseMemberService()->getExerciseMember($exercise['id'], $user['id']);

        if ($member && in_array($member['role'], ['teacher', 'student'])) {
            return true;
        }

        if ($user->hasPermission('admin_v2_item_bank_exercise')) {
            return true;
        }

        return false;
    }

    protected function validateExpiryMode($exercise)
    {
        $expiryMode = ExpiryModeFactory::create($exercise['expiryMode']);

        return $expiryMode->validateExpiryMode($exercise);
    }

    private function processFields($exercise, $fields)
    {
        if (in_array($exercise['status'], ['published', 'closed'])) {
            //发布或者关闭，不允许修改模式，但是允许修改时间
            unset($fields['expiryMode']);
            if ('published' == $exercise['status']) {
                //发布后，不允许修改时间
                unset($fields['expiryDays']);
                unset($fields['expiryStartDate']);
                unset($fields['expiryEndDate']);
            }
        }

        if (empty($fields['price']) || $fields['price'] <= 0) {
            $fields['isFree'] = 1;
        } else {
            $fields['isFree'] = 0;
            $fields['originPrice'] = $fields['price'];
        }

        return $fields;
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->biz->service('Content:FileService');
    }

    protected function hasAdminRole()
    {
        $user = $this->getCurrentUser();

        return $user->hasPermission('admin_v2_item_bank_exercise_content_manage');
    }

    public function canLearnExercise($exerciseId)
    {
        return $this->biz['item_bank_exercise.learn_chain']->process($this->get($exerciseId));
    }

    public function canJoinExercise($exerciseId)
    {
        return $this->biz['item_bank_exercise.join_chain']->process($this->get($exerciseId));
    }

    public function freeJoinExercise($exerciseId)
    {
        $access = $this->canJoinExercise($exerciseId);
        if (AccessorInterface::SUCCESS != $access['code']) {
            throw new UnableJoinException($access['msg'], $access['code']);
        }

        $exercise = $this->get($exerciseId);

        if ((1 == $exercise['isFree'] || 0 == $exercise['originPrice']) && $exercise['joinEnable']) {
            return $this->getExerciseMemberService()->becomeStudent(
                $exercise['id'],
                $this->getCurrentUser()->getId(),
                [
                    'remark' => OperateReason::JOIN_BY_FREE,
                    'reason' => OperateReason::JOIN_BY_FREE,
                    'reasonType' => OperateReason::JOIN_BY_FREE_TYPE,
                ]
            );
        }
    }

    protected function _prepareCourseConditions($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if (0 == $value) {
                return true;
            }

            return !empty($value);
        });

        return $conditions;
    }

    public function findExercisesByLikeTitle($title)
    {
        return $this->getExerciseDao()->findByLikeTitle($title);
    }

    public function tryTakeExercise($exerciseId)
    {
        $exercise = $this->get($exerciseId);
        $user = $this->getCurrentUser();
        if (empty($exercise)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE());
        }
        if (!$this->canTakeItemBankExercise($exercise['id'])) {
            $this->createNewException(ItemBankExerciseException::FORBIDDEN_TAKE_EXERCISE());
        }
        $member = $this->getExerciseMemberDao()->getByExerciseIdAndUserId($exercise['id'], $user['id']);

        return [$exercise, $member];
    }

    /**
     * @return ExerciseDao
     */
    protected function getExerciseDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseDao');
    }

    /**
     * @return ExerciseMemberDao
     */
    protected function getExerciseMemberDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseMemberDao');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->createService('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getExerciseModuleService()
    {
        return $this->createService('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return ExerciseModuleDao
     */
    protected function getItemBankExerciseModuleDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseModuleDao');
    }

    /**
     * @return AssessmentExerciseDao
     */
    protected function getAssessmentExerciseDao()
    {
        return $this->createDao('ItemBankExercise:AssessmentExerciseDao');
    }

    /**
     * @return AssessmentExerciseRecordDao
     */
    protected function getAssessmentExerciseRecordDao()
    {
        return $this->createDao('ItemBankExercise:AssessmentExerciseRecordDao');
    }

    /**
     * @return ChapterExerciseRecordDao
     */
    protected function getChapterExerciseRecordDao()
    {
        return $this->createDao('ItemBankExercise:ChapterExerciseRecordDao');
    }

    /**
     * @return MemberOperationRecordDao
     */
    protected function getMemberOperationRecordDao()
    {
        return $this->createDao('ItemBankExercise:MemberOperationRecordDao');
    }

    /**
     * @return ExerciseQuestionRecordDao
     */
    protected function getExerciseQuestionRecordDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseQuestionRecordDao');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->createService('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return ProductMallGoodsRelationService
     */
    private function getProductMallGoodsRelationService()
    {
        return $this->createService('MarketingMallBundle:ProductMallGoodsRelation:ProductMallGoodsRelationService');
    }
}
