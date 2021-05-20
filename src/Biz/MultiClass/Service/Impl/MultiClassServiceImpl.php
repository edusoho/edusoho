<?php

namespace Biz\MultiClass\Service\Impl;

use Biz\BaseService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Dao\MultiClassDao;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassProductService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\System\Service\LogService;

class MultiClassServiceImpl extends BaseService implements MultiClassService
{
    public function findByProductIds(array $productIds)
    {
        return $this->getMultiClassDao()->findByProductIds($productIds);
    }

    public function getMultiClass($id)
    {
        return $this->getMultiClassDao()->get($id);
    }

    public function countMultiClassCopyEd($id)
    {
        return $this->getMultiClassDao()->count(['copyId' => $id]);
    }

    public function createMultiClass($fields)
    {
        $teacherId = [
            [
                'id' => $fields['teacherId'],
                'isVisable' => 1,
            ],
        ];
        $assistantIds = $fields['assistantIds'];
        $fields = $this->filterMultiClassFields($fields);

        $this->beginTransaction();
        try {
            $multiClass = $this->getMultiClassDao()->create($fields);
            $this->getCourseMemberService()->setCourseTeachers($fields['courseId'], $teacherId, $multiClass['id']);
            $this->getCourseMemberService()->setCourseAssistants($fields['courseId'], $assistantIds, $multiClass['id']);

            $this->getLogService()->info(
                'multi_class',
                'create_multi_class',
                "创建班课#{$multiClass['id']}《{$fields['title']}》",
                $fields
            );

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $multiClass;
    }

    public function updateMultiClass($id, $fields)
    {
        $multiClassExisted = $this->getMultiClassDao()->get($id);
        if (empty($multiClassExisted)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $teacherId = [
            [
                'id' => $fields['teacherId'],
                'isVisable' => 1,
            ],
        ];
        $assistantIds = $fields['assistantIds'];

        $fields = $this->filterMultiClassFields($fields);

        $this->beginTransaction();
        try {
            $multiClass = $this->getMultiClassDao()->update($id, $fields);
            $this->getCourseMemberService()->setCourseTeachers($fields['courseId'], $teacherId, $multiClass['id']);
            $this->getCourseMemberService()->setCourseAssistants($fields['courseId'], $assistantIds, $multiClass['id']);

            $this->getLogService()->info(
                'multi_class',
                'update_multi_class',
                "更新班课#{$multiClass['id']}《{$fields['title']}》",
                $fields
            );

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $multiClass;
    }

    public function deleteMultiClass($id)
    {
        $multiClassExisted = $this->getMultiClassDao()->get($id);
        if (empty($multiClassExisted)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $this->beginTransaction();
        try {
            $this->getCourseMemberService()->releaseMultiClassMember($multiClassExisted['courseId'], $multiClassExisted['id']);
            $this->getMultiClassDao()->delete($id);

            $this->getLogService()->info(
                'multi_class',
                'delete_multi_class',
                "删除班课#{$id}《{$multiClassExisted['title']}》"
            );

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function cloneMultiClass($id)
    {
        $multiClass = $this->getMultiClassDao()->get($id);
        $this->beginTransaction();
        try {
            $number = $this->countMultiClassCopyEd($id);
            $number = 0 == $number ? '' : $number;
            $defaultProduct = $this->getMultiClassProductService()->getDefaultProduct();

            $this->biz['multi_class_copy']->copy($multiClass, [
                'number' => $number,
                'productId' => $defaultProduct ? $defaultProduct['id'] : 1,
                ]);

            $this->getLogService()->info(
                'multi_class',
                'clone_multi_class',
                "复制班课 - {$multiClass['title']}(#{$id}) 成功",
                ['multiClassId' => $id]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();

            $this->getLogService()->error(
                'multi_class',
                'clone_multi_class',
                "复制班课 - {$multiClass['title']}(#{$id}) 失败",
                ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function getMultiClassByTitle($title)
    {
        return $this->getMultiClassDao()->getByTitle($title);
    }

    private function filterMultiClassFields($fields)
    {
        if (isset($fields['teacherId'])) {
            unset($fields['teacherId']);
        }
        if (isset($fields['assistantIds'])) {
            unset($fields['assistantIds']);
        }

        return $fields;
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return MultiClassDao
     */
    protected function getMultiClassDao()
    {
        return $this->createDao('MultiClass:MultiClassDao');
    }

    /**
     * @return MultiClassProductService
     */
    protected function getMultiClassProductService()
    {
        return $this->createService('MultiClass:MultiClassProductService');
    }
}
