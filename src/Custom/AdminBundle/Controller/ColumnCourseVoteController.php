<?php
namespace Custom\AdminBundle\Controller;

use Topxia\AdminBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Service\Common\ServiceException;


class ColumnCourseVoteController extends BaseController
{

	public function indexAction(Request $request)
	{
		$total = $this->getColumnCourseVoteService()->getAllCourseVoteCount();
		$paginator = new Paginator($request, $total, 20);
		$courseVotes = $this->getColumnCourseVoteService()->findAllCourseVote($paginator->getOffsetCount(), $paginator->getPerPageCount());
		return $this->render('CustomAdminBundle:ColumnCourseVote:index.html.twig', array(
			'courseVotes' => $courseVotes,
			'paginator' => $paginator
		));
	}

	public function createAction(Request $request)
	{
		$columnOptions=array();
		$total = $this->getColumnService()->getAllColumnCount();
		$columns = $this->getColumnService()->findAllColumns(0, $total);
		foreach ($columns as $key => $value) {
			$columnId = $value['id'];
			$columnName = $value['name'];
			$columnOptions[$columnId]=$columnName;
		}

		if ('POST' == $request->getMethod()) {
			$courseVote = $this->getColumnCourseVoteService()->addColumnCourseVote($request->request->all());
			return $this->render('CustomAdminBundle:ColumnCourseVote:list-tr.html.twig', array('courseVotes' => $courseVotes));
		}

		return $this->render('CustomAdminBundle:ColumnCourseVote:courseVote-modal.html.twig', array(
			'courseVotes' => $this->newCourseVotes()
			,'columnOptions'=>$columnOptions
		));
	}

	private function newCourseVotes(){
		return  array('specialColumnId' =>''
			,'isShow'=>'none'
			,'courseAName'=>'' 
			,'courseACount'=>''
			,'courseBName'=>''
			,'courseBCount'=>''
			,'courseVoteCount'=>''
			,'voteStartTime'=>''
			,'voteEndTime'=>''
			,'id'=>'');
	}

	public function updateAction(Request $request, $id)
	{
		$column = $this->getColumnService()->getColumn($id);
		if (empty($column)) {
			throw $this->createNotFoundException();
		}

		if ('POST' == $request->getMethod()) {
			$column = $this->getColumnService()->updateColumn($id, $request->request->all());
			return $this->render('CustomAdminBundle:Column:list-tr.html.twig', array(
				'column' => $column
			));
		}

		return $this->render('CustomAdminBundle:Column:column-modal.html.twig', array(
			'column' => $column
		));
	}

	public function deleteAction(Request $request, $id)
	{
		$this->getColumnService()->deleteColumn($id);
		return $this->createJsonResponse(true);
	}

	public function checkNameAction(Request $request)
	{
		$name = $request->query->get('value');
		$exclude = $request->query->get('exclude');

		$avaliable = $this->getColumnService()->isColumnNameAvalieable($name, $exclude);

		if ($avaliable) {
			$response = array('success' => true, 'message' => '');
		} else {
			$response = array('success' => false, 'message' => '专栏已存在');
		}

			return $this->createJsonResponse($response);
	}

	private function getColumnCourseVoteService()
	{
		return $this->getServiceKernel()->createService('Custom:ColumnCourseVote.ColumnCourseVoteService');
	}

	private function getColumnService()
	{
		return $this->getServiceKernel()->createService('Custom:Taxonomy.ColumnService');
	}



}