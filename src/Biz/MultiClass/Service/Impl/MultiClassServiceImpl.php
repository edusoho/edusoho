<?php

namespace Biz\MultiClass\Service\Impl;

use Biz\BaseService;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
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
                'multiClass',
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
            $this->getCourseMemberService()->releaseMultiClassMember($multiClassExisted['courseId'], $multiClassExisted['id']);
            $multiClass = $this->getMultiClassDao()->update($id, $fields);
            $this->getCourseMemberService()->setCourseTeachers($fields['courseId'], $teacherId, $multiClass['id']);
            $this->getCourseMemberService()->setCourseAssistants($fields['courseId'], $assistantIds, $multiClass['id']);
            $this->getLogService()->info(
                'multiClass',
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
                'multiClass',
                'delete_multi_class',
                "删除班课#{$id}《{$multiClassExisted['title']}》"
            );

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function searchMultiClass($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->filterConditions($conditions);

        return $this->getMultiClassDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countMultiClass($conditions)
    {
        $conditions = $this->filterConditions($conditions);

        return $this->getMultiClassDao()->count($conditions);
    }

    public function getMultiClassByTitle($title)
    {
        return $this->getMultiClassDao()->getByTitle($title);
    }

    private function filterConditions($conditions)
    {
        if (empty($conditions)) {
            return [];
        }
        if (isset($conditions['ids']) && empty($conditions['ids'])) {
            $conditions['ids'] = [-1];
        }
        if (isset($conditions['courseIds']) && empty($conditions['courseIds'])) {
            $conditions['courseIds'] = [-1];
        }

        return $conditions;
    }

    private function filterMultiClassFields($fields)
    {
        if (isset($fields['teacherId'])) {
            unset($fields['teacherId']);
        }
        if (isset($fields['assistantIds'])) {
            unset($fields['assistantIds']);
        }

        if (isset($fields['courseId']) && !empty($fields['courseId'])) {
            $course = $this->getCourseService()->getCourse($fields['courseId']);
            if (empty($course)) {
                throw CourseException::NOTFOUND_COURSE();
            }
        }
        if (isset($fields['productId']) && !empty($fields['productId'])) {
            $course = $this->getMultiClassProductService()->getProduct($fields['productId']);
            if (empty($course)) {
                throw MultiClassException::PRODUCT_NOT_FOUND();
            }
        }

        return $fields;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MultiClassProductService
     */
    protected function getMultiClassProductService()
    {
        return $this->createService('MultiClass:MultiClassProductService');
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
}
