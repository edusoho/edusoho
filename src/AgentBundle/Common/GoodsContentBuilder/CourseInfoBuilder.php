<?php

namespace MarketingMallBundle\Common\GoodsContentBuilder;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\CourseStrategy;

class CourseInfoBuilder extends AbstractBuilder
{
    const TASKS_ALLOWED_KEY = ['title', 'type', 'number', 'counts', 'children', 'isPublish', 'activityType'];

    private $blankChapter = ['title' => '未分类章', 'type' => 'chapter', 'isPublish' => 1, 'number' => 0, 'counts' => ['unitNum' => 0, 'lessonNum' => 0, 'taskNum' => 0], 'children' => []];

    public function build($id)
    {
        $course = $this->getCourseService()->getCourse($id);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE);
        }

        return $this->buildCourseData($course);
    }

    protected function buildCourseData($course){
        $childrenCourseIds = [];
        $teachers = [];
        $result = $this->publicCourseData($course);
        $teacherIds = ArrayToolkit::column($this->getCourseService()->findTeachersByCourseId($course['id']),'userId');
        foreach ($teacherIds as $teacherId) {
            $teachers[] = $this->getTeacherInfoBuilder()->build($teacherId);
        }

        return [
            'courseIds' => array_merge([$course['id']], $childrenCourseIds),
            'title' => $result['count'] == 1 ? $result['courseSet']['title'] : $course['courseSetTitle'] . '(' . $course['title'] . ')',
            'subtitle' => $result['count'] == 1 ? $result['courseSet']['subtitle'] : $course['subtitle'],
            'cover' => $this->transformCover($result['courseSet']['cover']),
            'price' => $course['price'],
            'summary' => $this->transformImages($result['courseSet']['summary']),
            'courseCatalogue' => $result['courseCatalogue'],
            'teacherList' => $teachers,
        ];
    }

    public function builds($ids)
    {
        $courses = $this->getCourseService()->findCoursesByIds($ids);

        if (empty($courses)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE);
        }

        return $this->buildCourseDatas($courses);
    }


    protected function buildCourseDatas($courses)
    {
        $goodsContent = [];

        foreach ($courses as $course) {
           $result = $this->publicCourseData($course);
           $teachers = [];
           $teacherIds = ArrayToolkit::column($this->getCourseService()->findTeachersByCourseId($course['id']),'userId');
            foreach ($teacherIds as $teacherId) {
                $teachers[] = $this->getTeacherInfoBuilder()->build($teacherId);
            }
            array_push($goodsContent, [
                'courseId' => $course['id'],
                'title' => $result['count'] == 1 ? $result['courseSet']['title'] : $course['courseSetTitle'] . '(' . $course['title'] . ')',
                'subtitle' => $result['count'] == 1 ? $result['courseSet']['subtitle'] : $course['subtitle'],
                'cover' => $this->transformCover($result['courseSet']['cover']),
                'price' => $course['price'],
                'summary' => $this->transformImages($result['courseSet']['summary']),
                'courseCatalogue' => $result['courseCatalogue'],
                'teacherList' => $teachers,
            ]);
        }

        return $goodsContent;
    }

    protected function publicCourseData($course)
    {
        $childrenCourseIds = [];

        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $count = $this->getCourseService()->countCoursesByCourseSetId($course['courseSetId']);
        if (0 == $course['parentId']) {
            $childrenCourseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($course['id'], 1), 'id');
        }

        $courseCatalogue = $this->buildCourseCatalogue($this->getCourseService()->findCourseItems($course['id']));

        return [
            'courseSet'=>$courseSet,
            'childrenCourseIds'=>$childrenCourseIds,
            'courseCatalogue'=>$courseCatalogue
        ];
    }


    protected function buildCourseCatalogue($courseItems)
    {
        return $this->convertToTree($courseItems);
    }

    /**
     * 章->节->课时 结构
     * 向上补全结构，向下补全至节
     */
    protected function convertToTree($items)
    {
        $treeItems = [];

        if (empty($items)) {
            return $treeItems;
        }

        $nowChapterIndex = $nowUnitIndex = -1;

        // 如果第一章上方还有内容，则归入未分类章
        if ('chapter' != $items[0]['type']) {
            $treeItems[] = $this->blankChapter;
            $lastItem = 'chapter';
            ++$nowChapterIndex;
        } else {
            $lastItem = 'default';
        }

        foreach ($items as $index => $item) {

            switch ($item['type']) {
                case 'chapter':
                    ++$nowChapterIndex;
                    $nowUnitIndex = -1; //新章创建后，应重置当前节
                    $item['isPublish'] = 1;
                    $item = ArrayToolkit::parts($item, self::TASKS_ALLOWED_KEY);
                    $treeItems[$nowChapterIndex] = $item;
                    $treeItems[$nowChapterIndex]['children'] = [];
                    break;

                case 'unit':
                    ++$nowUnitIndex;
                    $item['isPublish'] = 1;
                    $item = ArrayToolkit::parts($item, self::TASKS_ALLOWED_KEY);
                    $treeItems[$nowChapterIndex]['children'][] = $item;
                    $treeItems[$nowChapterIndex]['children'][$nowUnitIndex]['children'] = [];
                    break;

                case 'lesson':
                    $lessons = $this->lessonSplit($item);
                    if ('unit' == $lastItem || $nowUnitIndex != -1) {
                        // 在对应节下面加入课程
                        $treeItems[$nowChapterIndex]['children'][$nowUnitIndex]['children'] = array_merge($treeItems[$nowChapterIndex]['children'][$nowUnitIndex]['children'], $lessons);
                    } else if ('chapter' == $lastItem || $nowUnitIndex == -1) {
                        // 在对应章下面加入课程
                        $treeItems[$nowChapterIndex]['children'] = array_merge($treeItems[$nowChapterIndex]['children'], $lessons);
                    }
                    break;

                default:
                    break;
            }

            $lastItem = $item['type'];
        }

        return $this->countChapterChildren($treeItems);
    }

    private function lessonSplit($item)
    {
        if (!isset($item['tasks'])) {
            return [];
        }

        $lessons = [];
        foreach ($item['tasks'] as $task) {
            $lessons[] = [
                'title' => $task['title'],
                'type' => $task['isLesson'] == 1 ? 'lesson' : 'task',
                'number' => $task['number'],
                'isPublish' => 'published' == $task['status'] ? 1 : 0,
                'activityType' => $task['type']
            ];
        }
        return $lessons;
    }

    private function countChapterChildren($trees)
    {
        foreach ($trees as $index => &$chapter) {
            $chapter['counts']['unitNum'] = $this->countByType($chapter['children'], 'unit');
            $chapter['counts']['lessonNum'] = $this->countByType($chapter['children'], 'lesson');
            $chapter['counts']['taskNum'] = $this->countByType($chapter['children'], 'task');
        }
        return $trees;
    }

    private function countByType($trees, $type)
    {
        $numbers = 0;
        foreach ($trees as $tree) {
            if ($tree['type'] == $type) {
                $numbers++;
            }
            if (isset($tree['children'])) {
                $numbers = $numbers + $this->countByType($tree['children'], $type);
            }
        }
        return $numbers;
    }

    /**
     * @param $course
     *
     * @return CourseStrategy
     */
    protected function createCourseStrategy($course)
    {
        return $this->biz->offsetGet('course.strategy_context')->createStrategy($course['courseType']);
    }

    /**
     * @return TeacherInfoBuilder
     */
    protected function getTeacherInfoBuilder()
    {
        return new TeacherInfoBuilder($this->biz);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
