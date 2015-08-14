<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\LiveClientFactory;
use Topxia\WebBundle\Controller\CourseController as CourseBaseController;

/**
 * 作业评价控制器.
**/
class CourseInfoController extends CourseBaseController
{
    public function indexAction(Request $request, $id)
    {
        list($course, $member) = $this->buildCourseLayoutData($request, $id);

        $category = $this->getCategoryService()->getCategory($course['categoryId']);
        $tags = $this->getTagService()->findTagsByIds($course['tags']);

        return $this->render('CustomWebBundle:CourseInfo:index.html.twig', array(
            'course' => $course,
            'member' => $member,
            'category' => $category,
            'tags' => $tags,
        ));
    }
}