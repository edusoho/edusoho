<?php
namespace Custom\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class DefaultController extends BaseController
{
	public function indexAction()
	{
        $currentUser = $this->getCurrentUser();

        $posts = $this->getPostService()->getPostsByUserId($currentUser['id']);
        $this->sortPosts($posts);
        $primaryPost = $this->getPostService()->getPrimaryPostByUserId($currentUser['id']);

        $postCoursePackages = $this->getPostCourseService()->findUserPostCoursePackagesWithStudyStatus($currentUser);
        $postCourseCategorys = $this->getTrainingCourseService()->findPostCourseCategory($postCoursePackages);

        if (!empty($postCoursePackages)) {
            $finishedCourses = $this->getTrainingCourseService()->getFinishedCourses($postCoursePackages);
            $passRate = count($finishedCourses).'/'.count($postCoursePackages);
            $passPercentage = round((count($finishedCourses)/count($postCoursePackages))*100);
        } else {
            $passRate = '0/0';
            $passPercentage = '0';
        }

        $currentCoursePackage = $this->getTrainingCourseService()->chooseCurrentCoursePackage($postCoursePackages);

        return $this->render('CustomWebBundle:Default:index.html.twig', array(
            'user' => $currentUser,
            'posts' => $posts,
            'primaryPost' => $primaryPost,
            'postCoursePackages' => $postCoursePackages,
            'postCourseCategorys' => $postCourseCategorys,
            'currentCoursePackage' => $currentCoursePackage,
            'passRate' => $passRate,
            'passPercentage' => $passPercentage
        ));
	}

    public function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getTrainingCourseService()
    {
        return $this->getServiceKernel()->createService('InternalTraining:TrainingCourse.TrainingCourseService');
    }

    protected function getPostService()
    {
        return $this->getServiceKernel()->createService('InternalTraining:Post.PostService');
    }

    protected function getPostMemberService()
    {
        return $this->getServiceKernel()->createService('InternalTraining:PostMember.PostMemberService');
    }

    protected function getPostCourseService()
    {
        return $this->getServiceKernel()->createService('InternalTraining:PostCourse.PostCourseService');
    }
    
    private function sortPosts(&$posts)
    {
        usort($posts, function ($a, $b) {
            if ($a['groupId'] != $b['groupId']) {
                $groupA = $this->getPostService()->getPostGroup($a['groupId']);
                $groupB = $this->getPostService()->getPostGroup($b['groupId']);
                return ($groupA['seq'] < $groupB['seq']) ? - 1 : 1;
            }
            if ($a['seq'] == $b['seq']) {
                return 0;
            }
            return ($a['seq'] < $b['seq']) ? - 1 : 1;
        });
    }
}