<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassProductService;
use Biz\MultiClass\Service\MultiClassService;

class MultiClass extends AbstractResource
{
    const MAX_ASSISTANT_NUMBER = 20;

    public function get(ApiRequest $request, $multiClassId)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $teachers = $this->getMemberService()->findMultiClassMemberByMultiClassIdAndRole($multiClass['id'], 'teacher');
        $multiClass['teacherIds'] = ArrayToolkit::column($teachers, 'userId');

        $assistants = $this->getMemberService()->findMultiClassMemberByMultiClassIdAndRole($multiClass['id'], 'assistant');;
        $multiClass['assistantIds'] = ArrayToolkit::column($assistants, 'userId');

        $this->getOCUtil()->single($multiClass, ['teacherIds', 'assistantIds']);
        $this->getOCUtil()->single($multiClass, ['courseId'], 'course');

        $product = $this->getMultiClassProductService()->getProduct($multiClass['productId']);
        $multiClass['product'] = empty($product) ? [] : $product;

        return $multiClass;
    }

    public function add(ApiRequest $request)
    {
        $multiClass = $this->checkDataFields($request->request->all());

        $existed = $this->getMultiClassService()->getMultiClassByTitle($multiClass['title']);

        if ($existed) {
            throw MultiClassException::MULTI_CLASS_EXIST();
        }

        return $this->getMultiClassService()->createMultiClass($multiClass);
    }

    public function update(ApiRequest $request, $id)
    {
        $multiClass = $this->checkDataFields($request->request->all());

        $existed = $this->getMultiClassService()->getMultiClassByTitle($multiClass['title']);

        if (!empty($existed) && $id != $existed['id']) {
            throw MultiClassException::MULTI_CLASS_EXIST();
        }

        return $this->getMultiClassService()->updateMultiClass($id, $multiClass);
    }

    public function remove(ApiRequest $request, $id)
    {
        $this->getMultiClassService()->deleteMultiClass($id);

        return ['success' => true];
    }

    private function checkDataFields($multiClass)
    {
        if (!ArrayToolkit::requireds($multiClass, ['title', 'courseId', 'productId'])) {
            throw MultiClassException::MULTI_CLASS_DATA_FIELDS_MISSING();
        }

        if (empty($multiClass['teacherId'])) {
            throw MultiClassException::MULTI_CLASS_TEACHER_REQUIRE();
        }

        if (empty($multiClass['assistantIds'])) {
            throw MultiClassException::MULTI_CLASS_ASSISTANT_REQUIRE();
        }

        if (count($multiClass['assistantIds']) > self::MAX_ASSISTANT_NUMBER) {
            throw MultiClassException::MULTI_CLASS_ASSISTANT_NUMBER_EXCEED();
        }

        return $multiClass;
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return MultiClassProductService
     */
    protected function getMultiClassProductService()
    {
        return $this->service('MultiClass:MultiClassProductService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
