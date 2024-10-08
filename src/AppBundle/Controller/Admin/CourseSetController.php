<?php

namespace AppBundle\Controller\Admin;

use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\Taxonomy\Service\TagService;
use VipPlugin\Biz\Vip\Service\LevelService;

class CourseSetController extends BaseController
{
    protected function filterCourseSetConditions($filter, $conditions)
    {
        if ('classroom' == $filter) {
            $conditions['parentId_GT'] = 0;
        } elseif ('vip' == $filter) {
            $conditions['isVip'] = 1;
            $conditions['parentId'] = 0;
        } else {
            $conditions['parentId'] = 0;
            $conditions = $this->filterCourseSetType($conditions);
        }

        $conditions = $this->fillOrgCode($conditions);

        if (!empty($conditions['categoryId'])) {
            $categorIds = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
            $categorIds[] = $conditions['categoryId'];
            $conditions['categoryIds'] = $categorIds;
            unset($conditions['categoryId']);
        }

        if (!empty($conditions['tagId'])) {
            $conditions['tagIds'] = [$conditions['tagId']];
            $conditions = $this->getCourseConditionsByTags($conditions);
        }

        return $conditions;
    }

    protected function filterCourseSetType($conditions)
    {
        if (!$this->getWebExtension()->isPluginInstalled('Reservation')) {
            $conditions['excludeTypes'] = ['reservation'];
        }

        return $conditions;
    }

    protected function getCourseConditionsByTags($conditions)
    {
        if (empty($conditions['tagIds'])) {
            return $conditions;
        }

        $tagOwnerIds = $this->getTagService()->findOwnerIdsByTagIdsAndOwnerType($conditions['tagIds'], 'course-set');

        $conditions['ids'] = empty($tagOwnerIds) ? [-1] : $tagOwnerIds;
        unset($conditions['tagIds']);

        return $conditions;
    }

    protected function getDifferentCourseSetsNum($conditions)
    {
        $total = $this->getCourseSetService()->countCourseSets($conditions);
        $published = $this->getCourseSetService()->countCourseSets(array_merge($conditions, ['status' => 'published']));
        $closed = $this->getCourseSetService()->countCourseSets(array_merge($conditions, ['status' => 'closed']));
        $draft = $this->getCourseSetService()->countCourseSets(array_merge($conditions, ['status' => 'draft']));

        return [
            'total' => empty($total) ? 0 : $total,
            'published' => empty($published) ? 0 : $published,
            'closed' => empty($closed) ? 0 : $closed,
            'draft' => empty($draft) ? 0 : $draft,
        ];
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return LevelService
     */
    protected function getVipLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }
}
