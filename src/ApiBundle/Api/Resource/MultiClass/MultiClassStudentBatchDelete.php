<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassService;

class MultiClassStudentBatchDelete extends AbstractResource
{
    /**
     * @param $id
     *
     * @return array
     *               删除
     */
    public function add(ApiRequest $request, $id)
    {
        $userIds = $request->request->get('userIds');
        if (empty($userIds) || !is_array($userIds)) {
            throw CommonException::ERROR_PARAMETER();
        }
        $multiClass = $this->getMultiClassService()->getMultiClass($id);
        if (empty($multiClass)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }
        $this->getCourseMemberService()->removeCourseStudents($multiClass['courseId'], $userIds);

        return [
            'success' => true,
            'message' => '批量删除成功',
        ];
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return MultiClassService
     */
    private function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }
}
