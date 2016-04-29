<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\BaseTestCase;

class CourseDeleteServiceTest extends BaseTestCase
{
    public function testdelete()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course = array(
            'title' => 'online test course '
        );
        $createCourse = $this->getCourseService()->createCourse($course);

        $status = array('courseId' => $createCourse['id'], 'type' => 'start_learn_lesson', 'properties' => '{"course":{"id":"3","title":"edusoho_java\u8bfe\u7a0b","picture":"","type":"normal","rating":"0","about":"","price":"0.00"},"lesson":{"id":"2","number":"1","type":"text","title":"edusoho_java\u8bfe\u7a0b_1","summary":"124214"}}');
        $this->getStatusService()->publishStatus($status);
        $count = $this->getStatusService()->searchStatusesCount(array('courseId' => $createCourse['id']));
        $this->assertEquals(1, $count);

        $announcementInfo = array(
            'targetType' => 'course',
            'targetId'   => $createCourse['id'],
            'content'    => 'test_announcement',
            'startTime'  => time(),
            'endTime'    => time() + 3600 * 1000,
            'url'        => 'http://www.baidu.com'
        );
        $createdAnnouncement = $this->getAnnouncementService()->createAnnouncement($announcementInfo);

        $userInfo = array(
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email'    => 'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $reviewInfo     = array(
            'title'    => 'title',
            'content'  => 'content',
            'rating'   => $createCourse['rating'],
            'userId'   => $registeredUser['id'],
            'courseId' => $createCourse['id']
        );
        $savedReview = $this->getReviewService()->saveReview($reviewInfo);

        $thread = array(
            'courseId' => $createCourse['id'],
            'type'     => 'discussion',
            'title'    => 'test thread',
            'content'  => 'test content'
        );
        $createdThread = $this->getThreadService()->createThread($thread);

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $createCourse['id'],
            'title'    => 'test lesson 1',
            'content'  => 'test lesson content 1',
            'type'     => 'text'
        ));

        $note = $this->getNoteService()->saveNote(array(
            'content'  => 'note content',
            'lessonId' => $lesson['id'],
            'courseId' => $createCourse['id']
        ));

        $lessonView = array('title' => 'lessonView', 'courseId' => $createCourse['id'], 'lessonId' => $lesson['id'], 'fileId' => '1', 'fileType' => 'video', 'fileStorage' => 'local', 'fileSource' => 'www.baidu.avi');
        $this->getCourseService()->createLessonView($lessonView);

        $courseLessonReplay = array('courseId' => $createCourse['id'], 'title' => '录播回放', 'lessonId' => $lesson['id'], 'replayId' => '1', 'userId' => $currentUser['id'], 'createdTime' => time());
        $courseLessonReplay = $this->getCourseService()->addCourseLessonReplay($courseLessonReplay);

        $draft = array(
            'userId'   => 1,
            'title'    => 'title',
            'courseId' => $createCourse['id'],
            'lessonId' => $lesson['id']
        );
        $this->getCourseService()->createCourseDraft($draft);

        $chapter        = array('courseId' => $createCourse['id'], 'title' => 'chapter 1', 'type' => 'chapter', 'number' => '1', 'seq' => '1');
        $createdChapter = $this->getCourseService()->createChapter($chapter);

        $target = 'course-'.$createCourse['id'];

        $testpaper = array('name' => 'Test', 'target' => $target, 'targetId' => $createCourse['id'], 'pattern' => 'QuestionType', 'ranges' => array('you' => 'isSB'), 'counts' => array('hello' => 'imstefanie'), 'missScores' => '-1');
        $testpaper = $this->getTestpaperService()->createTestpaper($testpaper);
        $this->assertEquals('Test', $testpaper[0]['name']);

        $question = array(
            'type'       => 'single_choice',
            'stem'       => 'question.',
            'difficulty' => 'normal',
            'answer'     => array('answer'),
            'target'     => $target,
            '"stem"'     => '测试',
            "choices"    => array("爱", "测", "额", "恶"),
            'uncertain'  => 0,
            "analysis"   => '',
            "score"      => '2',
            "submission" => 'submit',
            "type"       => "choice",
            "parentId"   => 0,
            'copyId'     => 1,
            "answer"     => "2"
        );
        $question = $this->getQuestionService()->createQuestion($question);

        $testpaper = array('name' => 'Test', "description" => '测试', "limitedTime" => '0', "mode" => "rand", "range" => "course", "ranges" => array(), "counts" => array("single_choice" => "1", "choice" => "0", "uncertain_choice" => "0", "fill" => "0", "determine" => "0", "material" => "0"), 'CopyId' => 1, 'target' => $target, "scores" => array("single_choice" => "2", "uncertain_choice" => "2", "choice" => "2", "uncertain_choice" => "2", "fill" => "2", "determine" => "2", "essay" => "2", "material" => "2"), "missScores" => array("choice" => 0, "uncertain_choice" => 0), "percentages" => array("simple" => "", "normal" => "", "difficulty" => ''), "target" => $target, "pattern" => "QuestionType", "copyId" => "1");

        $testpaper = $this->getTestpaperService()->createTestpaper($testpaper);
        $this->assertEquals('Test', $testpaper[0]['name']);

        $types = array('questions', 'testpapers', 'materials', 'chapters', 'drafts', 'lessons', 'lessonLearns', 'lessonReplays', 'lessonViews', 'favorites', 'notes', 'threads', 'reviews', 'announcements', 'statuses', 'members', 'course');

        foreach ($types as $type) {
            $this->getCourseDeleteService()->delete($createCourse['id'], $type);
        }

        $questionCount = $this->getQuestionDao()->searchQuestionsCount(array('targetPrefix' => "course-{$createCourse['id']}"));
        $this->assertEquals(0, $questionCount);
        $testpaperCount = $this->getTestpaperDao()->searchTestpapersCount(array('target' => "course-{$createCourse['id']}"));
        $this->assertEquals(0, $testpaperCount);
        $materialCount = $this->getMaterialDao()->searchMaterialCount(array('courseId' => $createCourse['id']));
        $this->assertEquals(0, $materialCount);
        $chapterCount = $this->getCourseChapterDao()->searchChapterCount(array('courseId' => $createCourse['id']));
        $this->assertEquals(0, $chapterCount);
        $draftCount = $this->getDraftDao()->searchDraftCount(array('courseId' => $createCourse['id']));
        $this->assertEquals(0, $draftCount);
        $lessonCount = $this->getLessonDao()->searchLessonCount(array('courseId' => $createCourse['id']));
        $this->assertEquals(0, $lessonCount);
        $lessonLearnCount = $this->getLessonLearnDao()->searchLearnCount(array('courseId' => $createCourse['id']));
        $this->assertEquals(0, $lessonLearnCount);
        $lessonReplayCount = $this->getCourseLessonReplayDao()->searchCourseLessonReplayCount(array('courseId' => $createCourse['id']));
        $this->assertEquals(0, $lessonReplayCount);
        $lessonViewCount = $this->getLessonViewDao()->searchLessonViewCount(array('courseId' => $createCourse['id']));
        $this->assertEquals(0, $lessonViewCount);
        $favoriteCount = $this->getFavoriteDao()->searchCourseFavoriteCount(array('courseId' => $createCourse['id']));
        $this->assertEquals(0, $favoriteCount);
        $noteCount = $this->getCourseNoteDao()->searchNoteCount(array('courseId' => $createCourse['id']));
        $this->assertEquals(0, $noteCount);
        $threadCount = $this->getThreadDao()->searchThreadCount(array('courseId' => $createCourse['id']));
        $this->assertEquals(0, $threadCount);
        $reviewCount = $this->getReviewDao()->searchReviewsCount(array('courseId' => $createCourse['id']));
        $this->assertEquals(0, $reviewCount);
        $announcementCount = $this->getAnnouncementDao()->searchAnnouncementsCount(array('targetId' => $createCourse['id'], 'targetType' => 'course'));
        $this->assertEquals(0, $announcementCount);
        $statusCount = $this->getStatusDao()->searchStatusesCount(array('courseId' => $createCourse['id']));
        $this->assertEquals(0, $statusCount);
        $memberCount = $this->getCourseMemberDao()->searchMemberCount(array('courseId' => $createCourse['id']));
        $this->assertEquals(0, $memberCount);
    }

    private function createUser()
    {
        $user              = array();
        $user['email']     = "user@user.com";
        $user['nickname']  = "user";
        $user['password']  = "user";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');
        return $user;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getStatusService()
    {
        return $this->getServiceKernel()->createService('User.StatusService');
    }

    protected function getAnnouncementService()
    {
        return $this->getServiceKernel()->createService('Announcement.AnnouncementService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }

    protected function getCourseDeleteService()
    {
        return $this->getServiceKernel()->createService('Course.CourseDeleteService');
    }

    protected function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    protected function getQuestionDao()
    {
        return $this->getServiceKernel()->createDao('Question.QuestionDao');
    }

    protected function getQuestionFavoriteDao()
    {
        return $this->getServiceKernel()->createDao('Question.QuestionFavoriteDao');
    }

    protected function getTestpaperResultDao()
    {
        return $this->getServiceKernel()->createDao('Testpaper.TestpaperResultDao');
    }

    protected function getTestpaperItemResultDao()
    {
        return $this->getServiceKernel()->createDao('Testpaper.TestpaperItemResultDao');
    }

    protected function getTestpaperItemDao()
    {
        return $this->getServiceKernel()->createDao('Testpaper.TestpaperItemDao');
    }

    protected function getTestpaperDao()
    {
        return $this->getServiceKernel()->createDao('Testpaper.TestpaperDao');
    }

    protected function getCourseDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseDao');
    }

    protected function getFavoriteDao()
    {
        return $this->getServiceKernel()->createDao('Course.FavoriteDao');
    }

    protected function getCourseNoteDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseNoteDao');
    }

    protected function getCourseNoteLikeDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseNoteLikeDao');
    }

    protected function getThreadDao()
    {
        return $this->getServiceKernel()->createDao('Course.ThreadDao');
    }

    protected function getThreadPostDao()
    {
        return $this->getServiceKernel()->createDao('Course.ThreadPostDao');
    }

    protected function getReviewDao()
    {
        return $this->getServiceKernel()->createDao('Course.ReviewDao');
    }

    protected function getAnnouncementDao()
    {
        return $this->getServiceKernel()->createDao('Announcement.AnnouncementDao');
    }

    protected function getStatusDao()
    {
        return $this->getServiceKernel()->createDao('User.StatusDao');
    }

    protected function getCourseMemberDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseMemberDao');
    }

    protected function getMaterialDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseMaterialDao');
    }

    protected function getCourseChapterDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseChapterDao');
    }

    protected function getDraftDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseDraftDao');
    }

    protected function getLessonDao()
    {
        return $this->getServiceKernel()->createDao('Course.LessonDao');
    }

    protected function getLessonLearnDao()
    {
        return $this->getServiceKernel()->createDao('Course.LessonLearnDao');
    }

    protected function getCourseLessonReplayDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseLessonReplayDao');
    }

    protected function getLessonViewDao()
    {
        return $this->getServiceKernel()->createDao('Course.LessonViewDao');
    }
}
