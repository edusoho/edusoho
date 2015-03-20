<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\AdminBundle\Controller\BaseController;
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
		return $this->render('TopxiaAdminBundle:Tag:index.html.twig', array(
			'tags' => $tags,
			'paginator' => $paginator
		));
	}

	public function createAction(Request $request)
	{
		if ('POST' == $request->getMethod()) {
			$tag = $this->getTagService()->addTag($request->request->all());
			return $this->render('TopxiaAdminBundle:Tag:list-tr.html.twig', array('tag' => $tag));
		}

		return $this->render('TopxiaAdminBundle:Tag:tag-modal.html.twig', array(
			'tag' => array('id' => 0, 'name' => '')
		));
	}

	public function updateAction(Request $request, $id)
	{
		$tag = $this->getTagService()->getTag($id);
		if (empty($tag)) {
			throw $this->createNotFoundException();
		}

		if ('POST' == $request->getMethod()) {
			$tag = $this->getTagService()->updateTag($id, $request->request->all());
			return $this->render('TopxiaAdminBundle:Tag:list-tr.html.twig', array(
				'tag' => $tag
			));
		}

		return $this->render('TopxiaAdminBundle:Tag:tag-modal.html.twig', array(
			'tag' => $tag
		));
	}

	public function deleteAction(Request $request, $id)
	{
		$this->getTagService()->deleteTag($id);
		return $this->createJsonResponse(true);
	}

	public function checkNameAction(Request $request)
	{
		$name = $request->query->get('value');
		$exclude = $request->query->get('exclude');

		$avaliable = $this->getTagService()->isTagNameAvalieable($name, $exclude);

        if ($avaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => '标签已存在');
        }

        return $this->createJsonResponse($response);
	}

	private function getTagService()
	{
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
	}

	private function getTagWithException($tagId)
	{
		$tag = $this->getTagService()->getTag($tagId);
		if (empty($tag)) {
			throw $this->createNotFoundException('标签不存在!');
		}
		return $tag;
	}

}