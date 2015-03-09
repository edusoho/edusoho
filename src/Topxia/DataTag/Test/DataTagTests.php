<?php
use Topxia\DataTag\HotGroupDataTag;
use Topxia\DataTag\CategoriesDataTag;
use Topxia\DataTag\CategoryAnnouncementDataTag;
use Topxia\DataTag\CourseDataTag;
use Topxia\DataTag\CategoryDataTag;
use Topxia\DataTag\CourseLessonsDataTag;
use Topxia\DataTag\CourseRankByHitDataTag;
use Topxia\DataTag\CourseRankByRatingDataTag;
use Topxia\DataTag\CourseRankByStudentDataTag;
use Topxia\DataTag\CourseRelatedArticlesDataTag;
use Topxia\DataTag\CourseReviewDataTag;
use Topxia\DataTag\CourseThreadDataTag;
use Topxia\DataTag\CourseThreadsByTypeDataTag;
use Topxia\DataTag\CoursesCountDataTag;
use Topxia\DataTag\EliteCourseThreadsByTypeDataTag;
use Topxia\DataTag\ElitedCourseQuestionsDataTag;
use Topxia\DataTag\ElitedCourseThreadsDataTag;
use Topxia\DataTag\FreeCoursesDataTag;
use Topxia\DataTag\FreeLessonsDataTag;
use Topxia\DataTag\HotThreadsDataTag;
use Topxia\DataTag\LatestArticlesDataTag;
use Topxia\DataTag\LatestCourseMembers2DataTag;
use Topxia\DataTag\LatestCourseMembersDataTag;
use Topxia\DataTag\LatestCourseQuestionsDataTag;
use Topxia\DataTag\LatestCourseReviewsDataTag;
use Topxia\DataTag\LatestCourseThreadsByTypeDataTag;
use Topxia\DataTag\LatestCourseThreadsDataTag;
use Topxia\DataTag\LatestCoursesDataTag;
use Topxia\DataTag\LatestFinishedLearnsDataTag;
use Topxia\DataTag\LatestLoginUsersDataTag;
use Topxia\DataTag\LatestTeachersDataTag;
use Topxia\DataTag\LatestUsersDataTag;
use Topxia\DataTag\NavigationDataTag;
use Topxia\DataTag\PersonDynamicDataTag;
use Topxia\DataTag\PopularCoursesByCategoryDataTag;
use Topxia\DataTag\PopularCoursesDataTag;
use Topxia\DataTag\PromotedTeacherDataTag;
use Topxia\DataTag\RecentLiveCoursesDataTag;
use Topxia\DataTag\RecommendCoursesDataTag;
use Topxia\DataTag\RecommendTeachersDataTag;
use Topxia\DataTag\TagsCoursesDataTag;
use Topxia\DataTag\TagsDataTag;
use Topxia\DataTag\TeacherCoursesDataTag;
use Topxia\DataTag\TopRatingCourseReviewsDataTag;
use Topxia\DataTag\UserDataTag;
use Topxia\DataTag\UserLatestLearnCoursesDataTag;
use Topxia\DataTag\UserandProfilesDataTag;
use Topxia\DataTag\VipLevelsDataTag;
use Topxia\DataTag\ClassroomsDataTag;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;

class DataTagTests extends PHPUnit_Framework_TestCase
{
    public function testCategoriesDataTag()
    {
        $Categories = new CategoriesDataTag();

        $categories = $Categories->getData(array('group'=>'course','parentId'=>"test"));

     /*   print_r($categories);*/
    }

    public function testCategoryAnnouncementDataTag()
    {
        $Announcement = new CategoryAnnouncementDataTag();

        $announcements = $Announcement->getData(array('count'=>'5','categoryId'=>"1"));

    /*        print_r($announcements);*/
    }

    public function testCategoryDataTag()
    {
        $Category = new CategoryDataTag();

        $data = $Category->getData(array('categoryId'=>"1"));

    /*         print_r($data);*/
    }

    public function testCourseDataTag()
    {
        $Course = new CourseDataTag();

        $data = $Course->getData(array('courseId'=>"1"));

/*             print_r($data);*/
    }

    public function testCourseLessonsDataTag()
    {
        $CourseLessons = new CourseLessonsDataTag();

        $data = $CourseLessons->getData(array('courseId'=>"1",'count'=>5));

/*             print_r($data);*/
    }

    public function testCourseRankByHitDataTag()
    {
        $CourseRank = new CourseRankByHitDataTag();

        $data = $CourseRank->getData(array('count'=>5));

/*             print_r($data);*/
    }

    public function testCourseRankByRatingDataTag()
    {
        $CourseRank = new CourseRankByRatingDataTag();

        $data = $CourseRank->getData(array('count'=>5));

/*             print_r($data);*/
    }

    public function testCourseRankByStudentDataTag()
    {
        $CourseRank = new CourseRankByStudentDataTag();

        $data = $CourseRank->getData(array('count'=>5));

/*             print_r($data);*/
    }
    //当课程的tags为空时 会报错
    public function testCourseRelatedArticlesDataTag()
    {
        $Article = new CourseRelatedArticlesDataTag();

        $data = $Article->getData(array('count'=>5,'courseId'=>1));

/*             print_r($data);*/
    }

    public function testCourseReviewDataTag()
    {
        $CourseReview = new CourseReviewDataTag();

        $data = $CourseReview->getData(array('count'=>5,'reviewId'=>1));
/*
             print_r($data);*/
    }

    public function testCourseThreadDataTag()
    {
        $CourseThread = new CourseThreadDataTag();

        $data = $CourseThread->getData(array('threadId'=>1,'courseId'=>1));
/*
             print_r($data);*/
    }

    public function testCourseThreadsByTypeDataTag()
    {
        $CourseThread = new CourseThreadsByTypeDataTag();

        $data = $CourseThread->getData(array('count'=>5));
/*
             print_r($data);*/
    }

    public function testCoursesCountDataTag()
    {
        $CoursesCount = new CoursesCountDataTag();

        $data = $CoursesCount->getData(array());

/*             print_r($data);*/
    }

    public function testEliteCourseThreadsByTypeDataTag()
    {
        $EliteCourseThreads = new EliteCourseThreadsByTypeDataTag();

        $data = $EliteCourseThreads->getData(array('count'=>5));

/*             print_r($data);*/
    }

    public function testElitedCourseQuestionsDataTag()
    {
        $ElitedCourseQuestions = new ElitedCourseQuestionsDataTag();

        $data = $ElitedCourseQuestions->getData(array('count'=>5,'courseId'=>1));

 /*            print_r($data);*/
    }

    public function testElitedCourseThreadsDataTag()
    {
        $ElitedCourseThreads = new ElitedCourseThreadsDataTag();

        $data = $ElitedCourseThreads->getData(array('count'=>5,'courseId'=>1));
/*
             print_r($data);*/
    }

    public function testFreeCoursesDataTag()
    {
        $FreeCourses = new FreeCoursesDataTag();

        $data = $FreeCourses->getData(array('count'=>5));

/*             print_r($data);*/
    }

    public function testFreeLessonsDataTag()
    {
        $FreeLessons = new FreeLessonsDataTag();

        $data = $FreeLessons->getData(array('count'=>5));
/*
             print_r($data);*/
    }

    public function testHotGroupDataTag()
    {
        $Group = new HotGroupDataTag();

        $groups = $Group->getData(array('count'=>5));

/*            print_r($groups);*/

    }

    public function testHotThreadsDataTag()
    {
        $Threads = new HotThreadsDataTag();

        $data = $Threads->getData(array('count'=>5));

/*            print_r($data);*/

    }

    public function testLatestArticlesDataTag()
    {
        $Articles = new LatestArticlesDataTag();

        $data = $Articles->getData(array('count'=>5));

/*            print_r($data);*/

    }


    public function testLatestCourseMembers2DataTag()
    {
        $Members = new LatestCourseMembers2DataTag();

        $data = $Members->getData(array('count'=>5));

/*            print_r($data);*/

    }

    public function testLatestCourseMembersDataTag()
    {
        $Members = new LatestCourseMembersDataTag();

        $data = $Members->getData(array('count'=>5));

/*            print_r($data);*/

    }

    public function testLatestCourseQuestionsDataTag()
    {
        $CourseQuestions = new LatestCourseQuestionsDataTag();

        $data = $CourseQuestions->getData(array('count'=>5));

/*            print_r($data);*/

    }

    public function testLatestCourseReviewsDataTag()
    {
        $CourseReviews = new LatestCourseReviewsDataTag();

        $data = $CourseReviews->getData(array('count'=>5));

/*            print_r($data);*/

    }

    public function testLatestCourseThreadsByTypeDataTag()
    {
        $CourseThreads = new LatestCourseThreadsByTypeDataTag();

        $data = $CourseThreads->getData(array('count'=>5));

/*            print_r($data);*/

    }

    public function testLatestCourseThreadsDataTag()
    {
        $CourseThreads = new LatestCourseThreadsDataTag();

        $data = $CourseThreads->getData(array('count'=>5,'courseId'=>1));

/*            print_r($data);*/

    }

    public function testLatestCoursesDataTag()
    {
        $Courses = new LatestCoursesDataTag();

        $data = $Courses->getData(array('count'=>5));

/*            print_r($data);*/

    }

    public function testLatestFinishedLearnsDataTag()
    {
        $FinishedLearns = new LatestFinishedLearnsDataTag();

        $data = $FinishedLearns->getData(array('count'=>5));

/*            print_r($data);*/

    }

    public function testLatestLoginUsersDataTag()
    {
        $LoginUsers = new LatestLoginUsersDataTag();

        $data = $LoginUsers->getData(array('count'=>5));

/*            print_r($data);*/

    }

    public function testLatestTeachersDataTag()
    {
        $Teachers = new LatestTeachersDataTag();

        $data = $Teachers->getData(array('count'=>5));

 /*           print_r($data);*/

    }

    public function testLatestUsersDataTag()
    {
        $Users = new LatestUsersDataTag();

        $data = $Users->getData(array('count'=>5));

 /*           print_r($data);*/

    }

    public function testNavigationDataTag()
    {
        $Navigation = new NavigationDataTag();

        $data = $Navigation->getData(array('type'=>'top'));
        $data = $Navigation->getData(array('type'=>'foot'));

/*            print_r($data);*/

    }

    public function testPersonDynamicDataTag()
    {
        $PersonDynamic = new PersonDynamicDataTag();

        $data = $PersonDynamic->getData(array('count'=>'5'));
/*
            print_r($data);
*/
    }

    public function testPopularCoursesByCategoryDataTag()
    {
        $PopularCourses = new PopularCoursesByCategoryDataTag();

        $data = $PopularCourses->getData(array('categoryId'=>1,'count'=>5));

 /*           print_r($data);*/

    }

    public function testPopularCoursesDataTag()
    {
        $PopularCourses = new PopularCoursesDataTag();

        $data = $PopularCourses->getData(array('type'=>'hitNum','count'=>5));

/*            print_r($data);*/

    }

    public function testPromotedTeacherDataTag()
    {
        $Teacher = new PromotedTeacherDataTag();

        $data = $Teacher->getData(array());

/*            print_r($data);*/

    }

    public function testRecentLiveCoursesDataTag()
    {
        $Course = new RecentLiveCoursesDataTag();

        $data = $Course->getData(array('count'=>5));

/*            print_r($data);*/

    }

    public function testRecommendCoursesDataTag()
    {
        $Course = new RecommendCoursesDataTag();

        $data = $Course->getData(array('count'=>5));

/*            print_r($data);*/

    }

    public function testRecommendTeachersDataTag()
    {
        $Teachers = new RecommendTeachersDataTag();

        $data = $Teachers->getData(array('count'=>5));

/*            print_r($data);*/

    }

    public function testTagsCoursesDataTag()
    {
        $Tags = new TagsCoursesDataTag();

        $data = $Tags->getData(array('count'=>5,'tags'=>array('默认标签')));

/*            print_r($data);*/

    }

    public function testTagsDataTag()
    {
        $Tags = new TagsDataTag();

        $data = $Tags->getData(array('count'=>5));

/*            print_r($data);*/

    }

    public function testTeacherCoursesDataTag()
    {
        $TeacherCourses = new TeacherCoursesDataTag();

        $data = $TeacherCourses->getData(array('userId'=>1,'count'=>5));

/*            print_r($data);*/

    }

    public function testTopRatingCourseReviewsDataTag()
    {
        $TopRatingCourse = new TopRatingCourseReviewsDataTag();

        $data = $TopRatingCourse->getData(array('courseId'=>1,'count'=>5));

/*            print_r($data);*/

    }

    public function testUserDataTag()
    {
        $Users = new UserDataTag();

        $data = $Users->getData(array('userId'=>1));

/*            print_r($data);*/

    }

    public function testUserLatestLearnCoursesDataTag()
    {
        $Users = new UserLatestLearnCoursesDataTag();

        $data = $Users->getData(array('userId'=>1,'count'=>5));

/*            print_r($data);*/

    }

    public function testUserandProfilesDataTag()
    {
        $Users = new UserandProfilesDataTag();

        $data = $Users->getData(array('userId'=>1));

/*            print_r($data);*/

    }

    public function testVipLevelsDataTag()
    {
        $Users = new VipLevelsDataTag();

        $data = $Users->getData(array('count'=>5));

/*            print_r($data);*/

    }

    public function testRecommendClassroomsDataTag()
    {
        $Classroom = new ClassroomsDataTag();

        $data = $Classroom->getData(array('count'=>5));

/*        print_r($data);*/
    }


    public function __construct()
    {
        $loader = require_once __DIR__.'/../../../../app/bootstrap.php.cache';
        Debug::enable();

        require_once __DIR__.'/../../../../app/AppKernel.php';

        $kernel = new AppKernel('dev', true);
        $kernel->loadClassCache();
        Request::enableHttpMethodParameterOverride();
        $request = Request::createFromGlobals();

        $kernel->boot();

        // START: init service kernel
        $serviceKernel = ServiceKernel::create($kernel->getEnvironment(), $kernel->isDebug());
        $serviceKernel->setEnvVariable(array(
            'host' => $request->getHttpHost(),
            'schemeAndHost' => $request->getSchemeAndHttpHost(),
            'basePath' => $request->getBasePath(),
            'baseUrl' =>  $request->getSchemeAndHttpHost() . $request->getBasePath(),
        ));

        $serviceKernel->setParameterBag($kernel->getContainer()->getParameterBag());
        $serviceKernel->setConnection($kernel->getContainer()->get('database_connection'));
        $serviceKernel->getConnection()->exec('SET NAMES UTF8');

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' =>  $request->getClientIp(),
            'roles' => array(),
        ));
        $serviceKernel->setCurrentUser($currentUser);
    }

}