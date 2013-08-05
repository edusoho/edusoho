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
		$total = $this->getTagService()->getAllTagsCount();
		$paginator = new Paginator($request, $total, 20);
		$tags = $this->getTagService()->getAllTags($paginator->getOffsetCount(), $paginator->getPerPageCount());
		return $this->render('TopxiaAdminBundle:Tag:index.html.twig', array(
			'tags' => $tags,
			'paginator' => $paginator
		));
	}

	public function createAction(Request $request)
	{
		$form = $this->getCreateForm();
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			if ($form->isValid()) {
				try {
					$tag = $this->getTagService()->addTag($form->getData());
					$html = $this->renderView('TopxiaAdminBundle:Tag:list-tr.html.twig', array('tag' => $tag));
					return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
				} catch (ServiceException $e) {
					return $this->createJsonResponse(array('status' => 'error', 'error' => array('message' => $e->getMessage())));
				}
			}
		}

		return $this->render('TopxiaAdminBundle:Tag:tag-modal.html.twig', array(
			'form' => $form->createView()
		));
	}

	public function updateAction(Request $request, $tag)
	{
		$tag = $this->getTagWithException($tag);
		$form = $this->getCreateForm($tag);
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			if ($form->isValid()) {
				try {
					$tag = $this->getTagService()->updateTag($tag['id'], $form->getData());
					$html = $this->renderView('TopxiaAdminBundle:Tag:list-tr.html.twig', array(
						'tag' => $tag
					));
					return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
				} catch (ServiceException $e) {
					return $this->createJsonResponse(array('status' => 'error', 'error' => array('message' => $e->getMessage())));
				}
			}
		}
		return $this->render('TopxiaAdminBundle:Tag:tag-modal.html.twig', array(
			'form' => $form->createView(),
			'tag' => $tag
		));
	}

	public function deleteAction(Request $request, $tag)
	{
		try {
			$this->getTagService()->deleteTag($tag);
			return $this->createJsonResponse(array('status' => 'ok'));
		} catch (ServiceException $e) {
			return $this->createJsonResponse(array('status' => 'error'));
		}
	}

	public function checkTagNameAction(Request $request, $tag)
	{
		$tagName = $request->query->get('value');
		$tagByName = $this->getTagService()->getTagByName($tagName);
		if (empty($tagByName) || (!empty($tagByName) && $tagByName['id'] == $tag)) {
			return $this->createJsonResponse(array('success' => true, 'message' => '标签名称可以使用'));
		}
		return $this->createJsonResponse(array('success' => false, 'message' => '标签已存在'));
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

	private function getCreateForm($tag = array())
	{
		$form = $this->createFormBuilder($tag)
			->add('name', 'text')
			->getForm();
		return $form;
	}
}