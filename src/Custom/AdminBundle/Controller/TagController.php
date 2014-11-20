<?php
namespace Custom\AdminBundle\Controller;
use Topxia\AdminBundle\Controller\BaseController as BaseController; 

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\FileToolkit;
use Topxia\Service\Common\ServiceException;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
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

 public function avatarAction(Request $request, $id)
    {
       $tag = $this->getTagService()->getTag($id);
        $user = $this->getUserService()->getUser($id);

        $form = $this->createFormBuilder()
            ->add('avatar', 'file')
            ->getForm();
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
                return $this->render('CustomAdminBundle:Tag:tag-avatar-crop-modal.html.twig', array(
                    'user' => $user,
                    'filename' => $fileName,
                    'pictureUrl' => $avatarData['pictureUrl'],
                    'naturalSize' => $avatarData['naturalSize'],
                    'scaledSize' => $avatarData['scaledSize'],
                    'tag'=>$tag
                ));
            }
        }


        return $this->render('CustomAdminBundle:Tag:tag-avatar-modal.html.twig', array(
            'form' => $form->createView(),
            'user' => $this->getUserService()->getUser($user['id']),
            'tag'=>$tag
        ));
    }
    public function avatarCropAction(Request $request, $id)
    {
        if($request->getMethod() == 'POST') {
            $options = $request->request->all();
            $filename = $request->query->get('filename');
            $filename = str_replace('!', '.', $filename);
            $filename = str_replace(array('..' , '/', '\\'), '', $filename);
            $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;
            $this->getCustomTagService()->changeTagAvatar($id, realpath($pictureFilePath), $options);
            return $this->createJsonResponse(true);
        }

        
    }


    private function avatar_2 ($id, $filename)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
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
	private function getTagService()
	{
        		return $this->getServiceKernel()->createService('Taxonomy.TagService');
	}
	private function getCustomTagService()
	{
       	 	return $this->getServiceKernel()->createService('Custom:Taxonomy.TagService');
	}

}