<?php

namespace ApiBundle\Api\Resource\Assistant;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassService;
use Biz\User\Service\UserService;

class Assistant extends AbstractResource
{
    /**
     * @return array
     * @ResponseFilter(class="ApiBundle\Api\Resource\Assistant\AssistantFilter", mode="simple")
     */
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->hasPermission('admin_v2_assistant')) {
            throw new AccessDeniedException();
        }

        $conditions = [
            'nickname' => $request->query->get('nickname', ''),
            'roles' => '|ROLE_TEACHER_ASSISTANT|',
            'destroyed' => 0,
            'locked' => 0,
            'excludeIds' => $request->query->get('excludeIds', []),
        ];

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $users = $this->getUserService()->searchUsers($conditions, ['createdTime' => 'DESC'], $offset, $limit);
        $total = $this->getUserService()->countUsers($conditions);

        $users = $this->appendAssistantData($users);

        return $this->makePagingObject($users, $total, $offset, $limit);
    }

    public function add(ApiRequest $request)
    {
        $assistantStudentData = $request->request->all();
        if (!ArrayToolkit::requireds($assistantStudentData, ['assistantId', 'studentIds', 'multiClassId'])) {
            throw MultiClassException::MULTI_CLASS_DATA_FIELDS_MISSING();
        }

        $assistantStudents = $this->getAssistantStudentService()->findByStudentIdsAndMultiClassId($assistantStudentData['studentIds'], $assistantStudentData['multiClassId']);
        $assistantStudents = ArrayToolkit::index($assistantStudents, 'studentId');

        $multiClass = $this->getMultiClassService()->getMultiClass($assistantStudentData['multiClassId']);
        foreach ($assistantStudentData['studentIds'] as $studentId) {
            if (empty($assistantStudents[$studentId])) {
                $fields = [
                    'studentId' => $studentId,
                    'multiClassId' => $assistantStudentData['multiClassId'],
                    'assistantId' => $assistantStudentData['assistantId'],
                    'courseId' => $multiClass['courseId'],
                ];

                $this->getAssistantStudentService()->create($fields);
            } else {
                $this->getAssistantStudentService()->update($assistantStudents[$studentId]['id'], ['assistantId' => $assistantStudentData['assistantId']]);
            }
        }

        return ['success' => true];
    }

    protected function appendAssistantData($assistants)
    {
        $currentTime = time();
        $members = $this->getMemberService()->findMembersByUserIdsAndRole(array_column($assistants, 'id'), 'assistant');

        $multiClassIds = empty($members) ? [-1] : array_column($members, 'multiClassId');
        $liveMultiClasses = $this->findMultiClassesByConditions(['ids' => $multiClassIds, 'startTimeGE' => $currentTime, 'endTimeLE' => $currentTime]);
        $endMultiClasses = $this->findMultiClassesByConditions(['ids' => $multiClassIds, 'endTimeLT' => $currentTime]);

        $courseIds = ArrayToolkit::column($liveMultiClasses, 'courseId');
        $liveMultiClassStudentCount = $this->findCourseStudentCount($courseIds);
        $endMultiClassStudentCount = $this->findCourseStudentCount($courseIds);
        $members = ArrayToolkit::group($members, 'userId');
        foreach ($assistants as &$assistant) {
            $assistantMembers = empty($members[$assistant['id']]) ? [] : $members[$assistant['id']];
            $liveMultiClassStudentNum = 0;
            $endMultiClassStudentNum = 0;
            foreach ($assistantMembers as $assistantMember) {
                $liveMultiClassStudentNum += empty($liveMultiClassStudentCount[$assistantMember['courseId']]) ? 0 : $liveMultiClassStudentCount[$assistantMember['courseId']]['count'];
                $endMultiClassStudentNum += empty($endMultiClassStudentCount[$assistantMember['courseId']]) ? 0 : $endMultiClassStudentCount[$assistantMember['courseId']]['count'];
            }
            $assistant['isScrmBind'] = empty($assistant['scrmUuid']) ? 0 : 1;
            $assistant['liveMultiClassStudentNum'] = $liveMultiClassStudentNum;
            $assistant['endMultiClassStudentNum'] = $endMultiClassStudentNum;
            $assistant['liveMultiClassNum'] = empty($liveMultiClasses[$assistant['id']]) ? 0 : count($liveMultiClasses[$assistant['id']]);
            $assistant['endMultiClassNum'] = empty($endMultiClasses[$assistant['id']]) ? 0 : count($endMultiClasses[$assistant['id']]);
        }

        return $assistants;
    }

    protected function findMultiClassesByConditions($conditions)
    {
        $multiClasses = $this->getMultiClassService()->searchMultiClass(
            $conditions,
            [],
            0,
            PHP_INT_MAX
        );

        return ArrayToolkit::group($multiClasses, 'userId');
    }

    protected function findCourseStudentCount($courseIds)
    {
        if (empty($courseIds)) {
            return [];
        }

        $courseStudentNum = $this->getMemberService()->searchMemberCountGroupByFields(
            ['courseIds' => $courseIds],
            'courseId',
            0,
            PHP_INT_MAX
        );

        return ArrayToolkit::index($courseStudentNum, 'courseId');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return AssistantStudentService
     */
    protected function getAssistantStudentService()
    {
        return $this->service('Assistant:AssistantStudentService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
