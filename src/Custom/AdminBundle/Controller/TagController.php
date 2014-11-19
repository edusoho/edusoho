<?php
namespace Custom\AdminBundle\Controller;
use Topxia\AdminBundle\Controller\BaseController as BaseController; 

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Service\Common\ServiceException;

class TagController extends BaseController
{

	public function indexAction(Request $request)
	{
		$total = $this->getTagService()->getAllTagCount();
		$paginator = new Paginator($request, $total, 20);
		$tags = $this->getTagService()->findAllTags($paginator->getOffsetCount(), $paginator->getPerPageCount());
		return $this->render('CustomAdminBundle:Tag:index.html.twig', array(
			'tags' => $tags,
			'paginator' => $paginator
		));
	}

	public function createAction(Request $request)
	{
		if ('POST' == $request->getMethod()) {
			$tag = $this->getCustomTagService()->addTag($request->request->all());
			return $this->render('CustomAdminBundle:Tag:list-tr.html.twig', array('tag' => $tag));
		}

		return $this->render('CustomAdminBundle:Tag:tag-modal.html.twig', array(
			'tag' => array('id' => 0, 'name' => '', 'description' => '')
		));
	}

	public function updateAction(Request $request, $id)
	{
		$tag = $this->getTagService()->getTag($id);
		if (empty($tag)) {
			throw $this->createNotFoundException();
		}

		if ('POST' == $request->getMethod()) {
			$tag = $this->getCustomTagService()->updateTag($id, $request->request->all());
			return $this->render('CustomAdminBundle:Tag:list-tr.html.twig', array(
				'tag' => $tag
			));
		}

		return $this->render('CustomAdminBundle:Tag:tag-modal.html.twig', array(
			'tag' => $tag
		));
	}


	private function getTagService()
	{
        		return $this->getServiceKernel()->createService('Taxonomy.TagService');
	}
	private function getCustomTagService()
	{
       	 	return $this->getServiceKernel()->createService('Custom:Taxonomy.TagService');
	}

}