<?php

namespace Biz\DiscoveryColumn\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseSetService;
use Biz\DiscoveryColumn\Service\DiscoveryColumnService;
use Topxia\Api\Resource\Classroom;

class DiscoveryColumnServiceImpl extends BaseService implements DiscoveryColumnService
{
    public function getDiscoveryColumn($id)
    {
        return $this->getDiscoveryColumnDao()->get($id);
    }

    public function updateDiscoveryColumn($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array('categoryId', 'orderType', 'type', 'showCount', 'title', 'seq'));

        return $this->getDiscoveryColumnDao()->update($id, $fields);
    }

    public function deleteDiscoveryColumn($id)
    {
        return $this->getDiscoveryColumnDao()->delete($id);
    }

    public function addDiscoveryColumn($fields)
    {
        return $this->getDiscoveryColumnDao()->create($fields);
    }

    public function findDiscoveryColumnByTitle($title)
    {
        return $this->getDiscoveryColumnDao()->findByTitle($title);
    }

    public function getAllDiscoveryColumns()
    {
        return $this->getDiscoveryColumnDao()->findAllOrderBySeq();
    }

    public function getDisplayData()
    {
        $columns = $this->getDiscoveryColumnDao()->findAllOrderBySeq();

        foreach ($columns as &$column) {
            if ($column['type'] == 'course' || $column['type'] == 'live') {
                $courseSets = $this->getCourseSetService()->searchCourseSets(
                    $this->determineConditions($column),
                    $this->determineSort($column),
                    0,
                    $column['showCount']
                );

                $column['data'] = $courseSets;
                $column['actualCount'] = count($courseSets);
            }

            if ($column['type'] == 'classroom') {
                $classrooms = $this->getClassroomService()->searchClassrooms(
                    $this->determineConditions($column),
                    $this->determineSort($column),
                    0,
                    $column['showCount']
                );

                $column['data'] = $classrooms;
                $column['actualCount'] = count($classrooms);
            }
        }

        return $columns;
    }

    private function determineConditions($column)
    {
        $conditions = array(
            'status' => 'published',
            'parentId' => 0,
            'showable' => 1,
        );

        if (!empty($column['categoryId'])) {
            $childrenIds = $this->getCategoryService()->findCategoryChildrenIds($column['categoryId']);
            $conditions['categoryIds'] = array_merge(array($column['categoryId']), $childrenIds);
        }

        if ($column['type'] == 'live') {
            $conditions['type'] = 'live';
        }

        if ($column['type'] == 'course') {
            $conditions['type'] = 'normal';
        }

        if ($column['orderType'] == 'recommend') {
            $conditions['recommended'] = 1;
        }

        return $conditions;
    }

    private function determineSort($column)
    {
        $sortMap = array(
            'hot' => array('studentNum' => 'DESC'),
            'recommend' => array(
                'recommendedSeq' => 'ASC',
                'recommendedTime' => 'DESC',
            ),
        );

        if ($column['orderType'] && !empty($sortMap[$column['orderType']])) {
            return $sortMap[$column['orderType']];
        } else {
            return array(
                'createdTime' => 'DESC',
            );
        }
    }

    public function sortDiscoveryColumns(array $ids)
    {
        $index = 1;
        foreach ($ids as $key => $id) {
            $this->updateDiscoveryColumn($id, array('seq' => $index));
            ++$index;
        }
    }

    protected function getDiscoveryColumnDao()
    {
        return $this->createDao('DiscoveryColumn:DiscoveryColumnDao');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }
}
