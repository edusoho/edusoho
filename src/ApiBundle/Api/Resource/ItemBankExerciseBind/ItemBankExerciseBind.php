<?php

namespace ApiBundle\Api\Resource\ItemBankExerciseBind;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\MemberService;
use Biz\ItemBankExercise\OperateReason;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\User\Service\UserService;

class ItemBankExerciseBind extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        try {
            $this->biz['db']->beginTransaction();
            $this->getItemBankExerciseService()->bindExercise($params['bindType'], $params['bindId'], $params['exerciseIds']);
            if ('course' == $params['bindType']) {
                $member = $this->getCourseMemberService()->findCourseStudents($params['bindId'], 0, PHP_INT_MAX);
            } else {
                $member = $this->getClassroomService()->findClassroomMembersByRole($params['bindId'], 'student', 0, PHP_INT_MAX);
            }
            $studentIds = array_column($member, 'userId');
            $userData = $this->getUserService()->findUsersByIds($studentIds);
            foreach ($params['exerciseIds'] as $exerciseId) {
                foreach ($userData as $key => $user) {
                    if (!empty($user['nickname'])) {
                        $user = $this->getUserService()->getUserByNickname($user['nickname']);
                    } else {
                        if (!empty($user['email'])) {
                            $user = $this->getUserService()->getUserByEmail($user['email']);
                        } else {
                            $user = $this->getUserService()->getUserByVerifiedMobile($user['verifiedMobile']);
                        }
                    }

                    $isExerciseMember = $this->getExerciseMemberService()->isExerciseMember($exerciseId, $user['id']);

                    if ($isExerciseMember) {
                        ++$existsUserCount;
                    } else {
                        $data = [
                            'price' => 0,
                            'remark' => empty($orderData['remark']) ? '通过批量导入添加' : $orderData['remark'],
                            'source' => 'outside',
                            'reason' => OperateReason::JOIN_BY_IMPORT,
                            'reasonType' => OperateReason::JOIN_BY_IMPORT_TYPE,
                        ];
                        $this->getExerciseMemberService()->becomeStudent($exerciseId, $user['id'], $data);

                        ++$successCount;
                    }
                }
            }
            $exerciseBinds = $this->getItemBankExerciseService()->findBindExercise($params['bindType'], $params['bindId']);
            $exerciseBindsIndex = ArrayToolkit::index($exerciseBinds, 'itemBankExerciseId');
            $exerciseAutoJoinRecords = [];
            foreach ($params['exerciseIds'] as $exerciseId) {
                foreach ($studentIds as $studentId) {
                    $exerciseAutoJoinRecords[] = [
                        'userId' => $studentId,
                        'itemBankExerciseId' => $exerciseId,
                        'itemBankExerciseBindId' => $exerciseBindsIndex[$exerciseId]['id'],
                    ];
                }
                $this->getItemBankExerciseService()->batchCreateExerciseAutoJoinRecord($exerciseAutoJoinRecords);
            }
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }

        return ['success' => true];
    }

    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $bindExercises = $this->getItemBankExerciseService()->findBindExercise($conditions['bindType'], $conditions['bindId']);
        $exerciseIds = array_values(array_unique(array_column($bindExercises, 'itemBankExerciseId')));
        $itemBankExercises = $this->getItemBankExerciseService()->findByIds($exerciseIds);

        foreach ($bindExercises as &$bindExercise) {
            $bindExercise['itemBankExercise'] = $itemBankExercises[$bindExercise['itemBankExerciseId']] ?? null;
            $bindExercise['chapterExerciseNum'] = 0;
            $bindExercise['assessmentNum'] = 0;
            $bindExercise['operateUser'] = $this->getUserService()->getUser(2);
        }
        // 绑定人
        // 章节练习数量

        // 试卷练习数量

        return $bindExercises;
    }

    public function remove(ApiRequest $request, $id)
    {
        $this->getItemBankExerciseService()->removeBindExercise($id);

        return ['success' => true];
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->service('ItemBankExercise:ExerciseMemberService');
    }
}
