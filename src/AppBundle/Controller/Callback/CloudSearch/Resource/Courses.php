<?php

namespace AppBundle\Controller\Callback\CloudSearch\Resource;

use AppBundle\Controller\Callback\CloudSearch\BaseProvider;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

/**
 * 课程资源集合(对应course_set表).
 */
class Courses extends BaseProvider
{
    public function get(Request $request)
    {
        $conditions = $request->query->all();
        $cursor = $request->query->get('cursor', time());
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 20);

        $conditions['status'] = 'published';
        $conditions['parentId'] = 0;
        $conditions['updatedTime_GE'] = $cursor;

        $courseSets = $this->getCourseSetService()->searchCourseSets($conditions, array('updatedTime' => 'ASC'), $start, $limit);
        $courseSets = $this->build($courseSets);
        $next = $this->nextCursorPaging($cursor, $start, $limit, $courseSets);

        return $this->wrap($this->filter($courseSets), $next);
    }

    public function filter($res)
    {
        return $this->multicallFilter('course', $res);
    }

    public function build($courseSets)
    {
        $courseSets = $this->buildCategories($courseSets);
        $courseSets = $this->buildTags($courseSets);
        $courseSets = $this->buildCourses($courseSets);

        return $courseSets;
    }

    protected function buildCategories($courseSets)
    {
        $categoryIds = ArrayToolkit::column($courseSets, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        foreach ($courseSets as &$courseSet) {
            if (isset($categories[$courseSet['categoryId']])) {
                $courseSet['category'] = array(
                    'id' => $categories[$courseSet['categoryId']]['id'],
                    'name' => $categories[$courseSet['categoryId']]['name'],
                );
            } else {
                $courseSet['category'] = array();
            }
        }

        return $courseSets;
    }

    protected function buildTags($courseSets)
    {
        $tagIdGroups = ArrayToolkit::column($courseSets, 'tags');
        $tagIds = ArrayToolkit::mergeArraysValue($tagIdGroups);

        $tags = $this->getTagService()->findTagsByIds($tagIds);

        foreach ($courseSets as &$courseSet) {
            $courseSetTagIds = $courseSet['tags'];
            $courseSet['tags'] = array();
            if (!empty($courseSetTagIds)) {
                foreach ($courseSetTagIds as $index => $courseSetTagId) {
                    if (isset($tags[$courseSetTagId])) {
                        $courseSet['tags'][$index] = array(
                            'id' => $tags[$courseSetTagId]['id'],
                            'name' => $tags[$courseSetTagId]['name'],
                        );
                    }
                }
            }
        }

        return $courseSets;
    }

    protected function buildCourses($courseSets)
    {
        $courseSets = ArrayToolkit::index($courseSets, 'id');
        $courseSetIds = ArrayToolkit::column($courseSets, 'id');
        $courses = $this->getCourseService()->findCoursesByCourseSetIds($courseSetIds);
        $courses = ArrayToolkit::group($courses, 'courseSetId');

        foreach ($courseSets as $index => $courseSet) {
            $courseSets[$index]['course'] = $courses[$index];
            $totalTaskNum = 0;
            foreach ($courses[$index] as $course) {
                $totalTaskNum += $course['taskNum'];
            }
            $courseSets[$index]['totalTaskNum'] = $totalTaskNum;
        }

        return $courseSets;
    }

    /**
     * @return Biz\Course\Service\CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return Biz\Taxonomy\Service\CategoryService
     */
    protected function getCategoryService()
    {
        return $this->getBiz()->service('Taxonomy:CategoryService');
    }

    /**
     * @return Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return Biz\Taxonomy\Service\TagService
     */
    protected function getTagService()
    {
        return $this->getBiz()->service('Taxonomy:TagService');
    }
}
