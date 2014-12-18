<?php
namespace Custom\AdminBundle\Controller;

use Topxia\AdminBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Service\Common\ServiceException;
use Topxia\Common\FileToolkit;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class ColumnController extends BaseController
{

	public function indexAction(Request $request)
	{
		$total = $this->getColumnService()->getAllColumnCount();
		$paginator = new Paginator($request, $total, 20);
		$columns = $this->getColumnService()->findAllColumns($paginator->getOffsetCount(), $paginator->getPerPageCount());
		return $this->render('CustomAdminBundle:Column:index.html.twig', array(
			'columns' => $columns,
			'paginator' => $paginator
		));
	}

	public function createAction(Request $request)
	{
		if ('POST' == $request->getMethod()) {
			$column = $this->getColumnService()->addColumn($request->request->all());
			return $this->render('CustomAdminBundle:Column:list-tr.html.twig', array('column' => $column));
		}

		return $this->render('CustomAdminBundle:Column:column-modal.html.twig', array(
			'column' => array('id' => 0, 'name' => '','description'=>'','code'=>'','weight'=>1,'classIndex'=>1,'subtitle'=>'')
		));
	}

	public function updateAction(Request $request, $id)
	{
		$column = $this->getColumnService()->getColumn($id);
		if (empty($column)) {
			throw $this->createNotFoundException();
		}
		//取该专栏下，初，中，高级课程的标签
		$lowLevelTags=$this->getColumnService()->findTagIdsByColumnIdAndCourseComplexity($id,'lowLevel');
		$middleLevelTags=$this->getColumnService()->findTagIdsByColumnIdAndCourseComplexity($id,'middleLevel');
		$highLevelTags=$this->getColumnService()->findTagIdsByColumnIdAndCourseComplexity($id,'highLevel');

		if ('POST' == $request->getMethod()) {

			$column = $this->getColumnService()->updateColumn($id, $request->request->all());
			return $this->render('CustomAdminBundle:Column:list-tr.html.twig', array(
				'column' => $column
			));
		}

		return $this->render('CustomAdminBundle:Column:column-modal.html.twig', array(
			'column' => $column,
			'lowLevelTags' => $lowLevelTags,
			'middleLevelTags'=>$middleLevelTags,
			'highLevelTags'=>$highLevelTags
		));
	}

	public function deleteAction(Request $request, $id)
	{
		$this->getColumnService()->deleteColumn($id);
		return $this->createJsonResponse(true);
	}


	public function checkCodeAction(Request $request)
	{
		$code = $request->query->get('value');
		$exclude = $request->query->get('exclude');

		$column = $this->getColumnService()->getColumnByCode($code);

		if (!empty($column) && $exclude != $column['code']) {
			$response = array('success' => false, 'message' => '专栏编码已经存在');
		} else {
			$response = array('success' => true, 'message' => '');
		}

		return $this->createJsonResponse($response);
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

	public function avatarAction(Request $request, $id)
	{
		$column = $this->getColumnService()->getColumn($id);
		$user = $this->getUserService()->getUser($id);

		$form = $this->createFormBuilder()->add('avatar', 'file')->getForm();
		if ($request->getMethod() == 'POST') {
		$form->bind($request);
		if ($form->isValid()) {
			$data = $form->getData();
			$file = $data['avatar'];

			if (!FileToolkit::isImageFile($file)) {
			return $this->createMessageResponse('error', '上传图片格式错误，请上传jpg, gif, png格式的文件。');
			}

			$filenamePrefix = "user_{$user['id']}_";
			$hash = substr(md5($filenamePrefix . time()), -8);
			$ext = $file->getClientOriginalExtension();
			$filename = $filenamePrefix . $hash . '.' . $ext;

			$directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
			$file = $file->move($directory, $filename);

			$fileName = str_replace('.', '!', $file->getFilename());

			$avatarData = $this->avatar_2($id, $fileName);
			return $this->render('CustomAdminBundle:Column:column-avatar-crop-modal.html.twig', array(
			'user' => $user,
			'filename' => $fileName,
			'pictureUrl' => $avatarData['pictureUrl'],
			'naturalSize' => $avatarData['naturalSize'],
			'scaledSize' => $avatarData['scaledSize'],
			'column'=>$column
			));
		}
	}


		return $this->render('CustomAdminBundle:Column:column-avatar-modal.html.twig', array(
		'form' => $form->createView(),
		'user' => $this->getUserService()->getUser($user['id']),
		'column'=>$column
		));
	}
	public function avatarCropAction(Request $request, $id)
	{
		if($request->getMethod() == 'POST') 
		{
			$options = $request->request->all();
			$filename = $request->query->get('filename');
			$filename = str_replace('!', '.', $filename);
			$filename = str_replace(array('..' , '/', '\\'), '', $filename);
			$pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;
			$this->getColumnService()->changeColumnAvatar($id, realpath($pictureFilePath), $options);
			return $this->createJsonResponse(true);
		}


	}


	private function avatar_2 ($id, $filename)
	{
		if (false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN'))
		 {
			throw $this->createAccessDeniedException();
		}

		$currentUser = $this->getCurrentUser();

		$filename = str_replace('!', '.', $filename);
		$filename = str_replace(array('..' , '/', '\\'), '', $filename);
		$pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

		try {
			$imagine = new Imagine();
			$image = $imagine->open($pictureFilePath);
		} catch (\Exception $e) {
			@unlink($pictureFilePath);
			return $this->createMessageResponse('error', '该文件为非图片格式文件，请重新上传。');
		}

		$naturalSize = $image->getSize();
		$scaledSize = $naturalSize->widen(270)->heighten(270);
		$pictureUrl = 'tmp/' . $filename;

		return array(
		'naturalSize' => $naturalSize,
		'scaledSize' => $scaledSize,
		'pictureUrl' => $pictureUrl
		);
	}

	private function getColumnService()
	{
		return $this->getServiceKernel()->createService('Custom:Taxonomy.ColumnService');
	}

	private function getColumnWithException($columnId)
	{
		$column = $this->getColumnService()->getColumn($columnId);
		if (empty($column)) {
			throw $this->createNotFoundException('标签不存在!');
		}
		return $column;
	}

}