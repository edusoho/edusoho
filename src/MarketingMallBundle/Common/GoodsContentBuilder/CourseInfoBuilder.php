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
    const COURSE_ALLOWED_KEY = ['courseIds', 'title', 'subtitle', 'cover', 'summary', 'courseCatalogue', 'teacherList'];

    const TASKS_ALLOWED_KEY = ['title', 'type', 'number', 'counts', 'children', 'isPublish', 'activityType'];

    private $blankChapter = ['title' => '未分类章', 'type' => 'chapter', 'isPublish' => 1, 'number' => 0, 'counts' =>['unitNum' => 0, 'lessonNum' => 0, 'taskNum' => 0], 'children' => []];

    public function build($id)
    {
        $course = $this->getCourseService()->getCourse($id);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE);
        }

        return $this->buildCourseData($course);
    }

    protected function buildCourseData($course)
    {
        $childrenCourseIds = [];
        $teachers = [];
        $courseSet = $this->getCourseSetService()->findCourseSetsByCourseIds([$course['id']])[1];
        if (0 == $course['parentId']) {
            $childrenCourseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($course['id'], 1), 'id');
        }
        $teacherIds = ArrayToolkit::column($this->getCourseService()->findTeachersByCourseId($course['id']), 'id');
        foreach ($teacherIds as $teacherId) {
            $teachers[] = $this->getTeacherInfoBuilder()->build($teacherId);
        }
        $courseCatalogue = $this->buildCourseCatalogue($this->getCourseService()->findCourseItems($course['id']));

        return [
            'courseIds' => array_merge([$course['id']], $childrenCourseIds),
            'title' => $course['courseSetTitle'],
            'subtitle' => $courseSet['subtitle'],
            'cover' => $this->transformCover($courseSet['cover']),
            'summary' => $courseSet['summary'],
            'courseCatalogue' => $courseCatalogue,
            'teacherList' => $teachers,
        ];
    }

    protected function buildCourseCatalogue($courseItems)
    {
        return $this->convertToTree($courseItems);
        $courseCatalogue = [];
        $chapterItems = [];
        $unitItems = [];
        $chapterIndex = -1;
        $unitIndex = -1;
        foreach ($courseItems as &$courseItem) {
            if ('chapter' == $courseItem['type']) {
                ++$chapterIndex;
                $unitIndex = -1;
                $courseItem['isPublish'] = 'published' == $courseItem['status'] ? 1 : 0;
                $courseItem = ArrayToolkit::parts($courseItem, self::TASKS_ALLOWED_KEY);
                $chapterItems[] = [$chapterIndex => $courseItem];
            }
            if ('unit' == $courseItem['type']) {
                ++$unitIndex;
                $courseItem['isPublish'] = 'published' == $courseItem['status'] ? 1 : 0;
                $courseItem = ArrayToolkit::parts($courseItem, self::TASKS_ALLOWED_KEY);
                $unitItems[$chapterIndex][] = [$unitIndex => $courseItem];
            }
            if (!empty($courseItem['tasks'])) {
                foreach ($courseItem['tasks'] as $key => &$tasks) {
                    $tasks['type'] = 0 == $key ? 'lesson' : 'tasks';
                    $tasks['activityType'] = $tasks['activity']['mediaType'];
                    $tasks['isPublish'] = 'published' == $tasks['status'] ? 1 : 0;
                    $tasks = ArrayToolkit::parts($tasks, self::TASKS_ALLOWED_KEY);
                    if (-1 == $unitIndex) {
                        $courseCatalogue[] = $tasks;
                    } else {
                        $unitItems[$chapterIndex][$unitIndex]['children'][] = $tasks;
                    }
                }
            }
        }
        foreach ($chapterItems as $key => &$chapter) {
            $tasksNum = 0;
            $lessonNum = 0;
            if (!empty($unitItems[$key])) {
                $chapter['children'] = $unitItems[$key];
                foreach ($chapter['children'] as $unit) {
                    if (!empty($unit['children'])) {
                        $groupedTasks = ArrayToolkit::group($unit['children'], 'type');
                        $tasksNum = empty($groupedTasks['tasks']) ? $tasksNum + 0 : $tasksNum + count($groupedTasks['tasks']);
                        $lessonNum = empty($groupedTasks['lesson']) ? $lessonNum + 0 : $lessonNum + count($groupedTasks['lesson']);
                    }
                }
            }
            $chapter['counts']['unitNum'] = count($chapter['children']);
            $chapter['counts']['tasksNum'] = $tasksNum;
            $chapter['counts']['lessonNum'] = $lessonNum;
            $chapter = ArrayToolkit::parts($chapter, self::TASKS_ALLOWED_KEY);
        }

        return array_merge($courseCatalogue, $chapterItems);
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
                    // 在对应章下面加入课程
                    if ('chapter' == $lastItem) {
                        $treeItems[$nowChapterIndex]['children'] = array_merge($treeItems[$nowChapterIndex]['children'],$lessons);
                    }else {
                        // 在对应节下面加入课程
                        $treeItems[$nowChapterIndex]['children'][$nowUnitIndex]['children'] = array_merge($treeItems[$nowChapterIndex]['children'][$nowUnitIndex]['children'], $lessons);
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
        if(!isset($item['tasks'])) {
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
            if($tree['type'] == $type) {
                $numbers ++;
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
