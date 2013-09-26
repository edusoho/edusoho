<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Course\CourseService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

class CourseServiceTest extends BaseTestCase
{

    public function testFavoriteCourse()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $createdCourse = $this->getCourseService()->createCourse($course);
        $result = $this->getCourseService()->favoriteCourse($createdCourse['id']);

        $this->assertTrue($result);
    }

     /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testFavoriteCourseTwice()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($course);

        $this->getCourseService()->favoriteCourse($course['id']);
        $this->getCourseService()->favoriteCourse($course['id']);
    }

    /**
     * @group current
     */
    public function testUnFavoriteCourse()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($course);

        $this->getCourseService()->favoriteCourse($course['id']);
        $result = $this->getCourseService()->unFavoriteCourse($course['id']);
        $this->assertTrue($result);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     * @group current
     */
    public function testUnFavoriteCourseTwice()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($course);

        $this->getCourseService()->favoriteCourse($course['id']);
        $result = $this->getCourseService()->unFavoriteCourse($course['id']);
        $result = $this->getCourseService()->unFavoriteCourse($course['id']);
    }

    /**
     * @group current
     */
    public function testHasFavoritedCourse()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($course);

        $this->assertFalse($this->getCourseService()->hasFavoritedCourse($course['id']));

        $this->getCourseService()->favoriteCourse($course['id']);
        $this->assertTrue($this->getCourseService()->hasFavoritedCourse($course['id']));
    }

    public function testCreateCourse()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $createdCourse = $this->getCourseService()->createCourse($course);

        $this->assertGreaterThan(0, $createdCourse['id']);
        $this->assertEquals($course['title'], $createdCourse['title']);

    }

    public function testCreateLesson()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($course);

        $lesson = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        );
        $createdLesson = $this->getCourseService()->createLesson($lesson);

        $this->assertGreaterThan(0,  $createdLesson['id']);
        $this->assertEquals($lesson['courseId'], $createdLesson['courseId']);
        $this->assertEquals($lesson['title'], $createdLesson['title']);
        $this->assertEquals($lesson['content'], $createdLesson['content']);
        $this->assertEquals(1, $createdLesson['number']);
        $this->assertEquals(1, $createdLesson['seq']);

        $lesson = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 2',
            'content' => 'test lesson content 2',
        );
        $createdLesson = $this->getCourseService()->createLesson($lesson);

        $this->assertEquals(2, $createdLesson['number']);
        $this->assertEquals(2, $createdLesson['seq']);
    }

    public function testDeleteLesson()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));

        $lesson1 = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        ));

        $lesson2 = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title' => 'test lesson 2',
            'content' => 'test lesson content 2',
        ));

        $lesson3 = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title' => 'test lesson 3',
            'content' => 'test lesson content 3',
        ));

        $this->getCourseService()->deleteLesson($course['id'], $lesson1['id']);

        $this->assertNull($this->getCourseService()->getCourseLesson($course['id'], $lesson1['id']));

        $number = 1;
        $lessons = $this->getCourseService()->getCourseLessons($course['id']);
        foreach ($lessons as $lesson) {
            $this->assertEquals($number, $lesson['number']);
            $number ++;
        }
    }

    public function testCreateChapter()
    {
        $chapter1 = array('courseId' => 1, 'title' => 'chapter 1');
        $chapter2 = array('courseId' => 1, 'title' => 'chapter 2');
        $chapter3 = array('courseId' => 1, 'title' => 'chapter 3');

        $createdChapter1 = $this->getCourseService()->createChapter($chapter1);

        $this->assertTrue(is_array($createdChapter1));
        $this->assertEquals($chapter1['courseId'], $createdChapter1['courseId']);
        $this->assertEquals($chapter1['title'], $createdChapter1['title']);
        $this->assertEquals(1, $createdChapter1['number']);
        $this->assertEquals(1, $createdChapter1['seq']);

        $createdChapter2 = $this->getCourseService()->createChapter($chapter2);
        $this->assertEquals(2, $createdChapter2['number']);
        $this->assertEquals(2, $createdChapter2['seq']);

        $createdChapter3 = $this->getCourseService()->createChapter($chapter3);
        $this->assertEquals(3, $createdChapter3['number']);
        $this->assertEquals(3, $createdChapter3['seq']);
    }

    public function testCreateLessonAndChapter()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));

        $chapter = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title' => 'chapter 1'
        ));

        $this->assertEquals(1, $chapter['number']);
        $this->assertEquals(1, $chapter['seq']);

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        ));
        $this->assertEquals(1, $lesson['number']);
        $this->assertEquals(2, $lesson['seq']);

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter['id'],
            'title' => 'test lesson 2',
            'content' => 'test lesson content 2',
        ));
        $this->assertEquals(2, $lesson['number']);
        $this->assertEquals(3, $lesson['seq']);

        $chapter = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title' => 'chapter 2'
        ));
        $this->assertEquals(2, $chapter['number']);
        $this->assertEquals(4, $chapter['seq']);
    }

    public function testDeleteChapter()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));

        $chapter1 = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title' => 'chapter 1'
        ));

        $lesson1 = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter1['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        ));

        $lesson2 = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter1['id'],
            'title' => 'test lesson 2',
            'content' => 'test lesson content 2',
        ));

        $chapter2 = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title' => 'chapter 2'
        ));

        $lesson3 = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter2['id'],
            'title' => 'test lesson 3',
            'content' => 'test lesson content 3',
        ));

        $chapter3 = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title' => 'chapter 3'
        ));

        $this->getCourseService()->deleteChapter($course['id'], $chapter2['id']);
        $this->assertNull($this->getCourseService()->getChapter($course['id'], $chapter2['id']));

        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lesson3['id']);
        $this->assertEquals($chapter1['id'], $lesson['chapterId']);

        $number = 1;
        $chapters = $this->getCourseService()->getCourseChapters($course['id']);
        foreach ($chapters as $chapter) {
            $this->assertEquals($number, $chapter['number']);
            $number ++;
        }

    }

    public function testGetCourseItems()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));
        $items = $this->createCourseItems($course);

        $this->assertEquals(5, count($items));
        $seq = 1;
        foreach ($items as $item) {
            $this->assertEquals($seq, $item['seq']);
            $seq++; 
        }
    }

    public function testSortCourseItems()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));
        $items = $this->createCourseItems($course);
        $itemIds = array_keys($items);
        shuffle($itemIds);

        $this->getCourseService()->sortCourseItems($course['id'], $itemIds);

        $items = $this->getCourseService()->getCourseItems($course['id']);

        $seq = 1;
        foreach ($items as $itemId => $item) {
            $this->assertEquals(array_shift($itemIds), $itemId);
            $this->assertEquals($seq, $item['seq']);
            $seq++;
        }
    }

    public function testStartLearnLesson()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => 0,
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        ));

        $user = $this->getCurrentUser();

        $this->getCourseService()->startLearnLesson($course['id'], $lesson['id']);

        $status = $this->getCourseService()->getUserLearnLessonStatus($user['id'],  $course['id'], $lesson['id']);
        $this->assertEquals('learning', $status);
    }

    public function testFinishLearnLesson()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => 0,
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        ));

        $user = $this->getCurrentUser();

        $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);

        $status = $this->getCourseService()->getUserLearnLessonStatus($user['id'],  $course['id'], $lesson['id']);
        $this->assertEquals('finished', $status);
    }

    public function testCancelLearnLesson()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => 0,
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        ));

        $user = $this->getCurrentUser();

        $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);
        $this->getCourseService()->cancelLearnLesson($course['id'], $lesson['id']);

        $status = $this->getCourseService()->getUserLearnLessonStatus($user['id'],  $course['id'], $lesson['id']);
        $this->assertEquals('learning', $status);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testSortCourseItemsWithLessItemIds()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));
        $items = $this->createCourseItems($course);
        $itemIds = array_keys($items);
        shuffle($itemIds);
        array_pop($itemIds);
        $this->getCourseService()->sortCourseItems($course['id'], $itemIds);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testSortCourseItemsWithMoreItemIds()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));
        $items = $this->createCourseItems($course);
        $itemIds = array_keys($items);
        $itemIds[] = 'lesson-99999';
        $this->getCourseService()->sortCourseItems($course['id'], $itemIds);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testSortCourseItemsWithErrorItemIds()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));
        $items = $this->createCourseItems($course);
        $itemIds = array_keys($items);
        shuffle($itemIds);
        array_pop($itemIds);
        $itemIds[] = 'lesson-99999';
        $this->getCourseService()->sortCourseItems($course['id'], $itemIds);
    }

    private function createCourseItems($course)
    {
        $chapter = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title' => 'chapter 1'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter['id'],
            'title' => 'test lesson 2',
            'content' => 'test lesson content 2',
        ));

        $chapter = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title' => 'chapter 2'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter['id'],
            'title' => 'test lesson 3',
            'content' => 'test lesson content 3',
        ));

        return $this->getCourseService()->getCourseItems($course['id']);
    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}