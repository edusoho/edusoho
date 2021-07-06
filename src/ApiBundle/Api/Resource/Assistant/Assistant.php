<?php

namespace ApiBundle\Api\Resource\Assistant;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Filter;
use AppBundle\Common\ArrayToolkit;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassService;
use Biz\User\Service\UserService;
use ApiBundle\Api\Annotation\ResponseFilter;

class Assistant extends AbstractResource
{
    /**
     * @return array
     * @Access(roles="ROLE_TEACHER_ASSISTANT,ROLE_TEACHER,ROLE_ADMIN,ROLE_SUPER_ADMIN,ROLE_EDUCATIONAL_ADMIN")
     * @ResponseFilter(class="ApiBundle\Api\Resource\Assistant\AssistantFilter", mode="simple")
     */
    public function search(ApiRequest $request)
    {
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
}
