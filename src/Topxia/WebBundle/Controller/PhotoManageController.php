<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\WebBundle\Form\ReviewType;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Common\FileToolkit;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class PhotoManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        return $this->forward('TopxiaWebBundle:PhotoManage:base',  array('id' => $id));
    }

    public function baseAction(Request $request, $id)
    {
        $photo = $this->getPhotoService()->getPhoto($id);
        $form = $this->createPhotoBaseForm($photo);
        if($request->getMethod() == 'POST'){
            $form->bind($request);
            if($form->isValid()){
                $photoBaseInfo = $form->getData();
                $this->getPhotoService()->updatePhoto($id, $photoBaseInfo);
                $this->setFlashMessage('success', '专辑基本信息已保存！');
                return $this->redirect($this->generateUrl('photoview_manage_base',array('id' => $id))); 
            }
        }
        return $this->render('TopxiaWebBundle:PhotoManage:base.html.twig', array(
            'photo' => $photo,
            'form' => $form->createView(),
        ));
    }

    public function infoAction(Request $request, $id)
    {
        $photoFile=$this->getPhotoService()->getPhotoFile($id);

        $form=$this->createPhotoFileBaseForm($photoFile);

        if($request->getMethod() == 'POST'){
            $form->bind($request);
            $photoBaseInfo = $form->getData();
            $this->getPhotoService()->updatePhotoFile($id, $photoBaseInfo);
            return $this->createJsonResponse(true);
        }

        return $this->render('TopxiaWebBundle:PhotoManage:picture-model.html.twig',
            array('form' => $form->createView(),
                "photofile"=>$photoFile));
    }

    private function createPhotoFileBaseForm($photofile)
    {
        $builder = $this->createNamedFormBuilder('photofile', $photofile)
            ->add('content', 'text');

        return $builder->getForm();
    }

   

    public function pictureAction(Request $request, $id)
    {
     
        $photo = $this->getPhotoService()->getPhoto($id);
        if($request->getMethod() == 'POST'){
            $file = $request->files->get('picture');

             if (!FileToolkit::isImageFile($file)) {
                    return $this->createMessageResponse('error', '上传图片格式错误，请上传jpg, gif, png格式的文件。');
                }


            $filenamePrefix = "photo_{$photo['id']}_";
            $hash = substr(md5($filenamePrefix . time()), -8);
            $filename = $filenamePrefix . $hash . '.' . $file->getClientOriginalExtension();

            $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';

            $file = $file->move($directory, $filename);
            return $this->redirect($this->generateUrl('photoview_manage_picture_crop', array(
                'id' => $photo['id'],
                'file' => $file->getFilename())
            ));
        }

        $conditions = $request->query->all();
        $conditions['groupId']=$photo['id'];
        $count = $this->getPhotoService()->searchFileCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $photos = $this->getPhotoService()->searchFiles($conditions,'latest', $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        return $this->render('TopxiaWebBundle:PhotoManage:picture.html.twig', array(
            'photo' => $photo,
            'photos' =>$photos
        ));
    }

    public function pictureCropAction(Request $request, $id)
    {
        //$course = $this->getCourseService()->tryManageCourse($id);
        $photo = $this->getPhotoService()->getPhoto($id);
        $filegroup=$this->getFileService()->getFileGroupByCode('photo');

        //@todo 文件名的过滤
        $filename = $request->query->get('file');
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);

        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

        if($request->getMethod() == 'POST') {
            $c = $request->request->all();
            $c['groupId']=$filegroup['id'];
            $c['url']=$this->saveFile($photo['id'],$pictureFilePath,$c);
            $c['groupId']=$photo['id'];
            $this->getPhotoService()->createPhotoFile($c);
            return $this->redirect($this->generateUrl('photoview_manage_picture', array('id' => $photo['id'])));
        }

        $imagine = new Imagine();
        $image = $imagine->open($pictureFilePath);

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(400);
        $pictureUrl = $this->container->getParameter('topxia.upload.public_url_path') . '/tmp/' . $filename;

        return $this->render('TopxiaWebBundle:PhotoManage:picture-crop.html.twig', array(
            'photo' => $photo,
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    private function saveFile($courseId, $filePath, array $options){
        $pathinfo = pathinfo($filePath);
        $imagine = new Imagine();
        $rawImage = $imagine->open($filePath);
        $largeImage = $rawImage->copy();
        
        $largeImage->crop(new Point($options['x'], $options['y']), new Box($options['width'], $options['height']));
        
        if(!empty($options['width'])){
            $options['width']=$options['width']>800?800:$options['width'];
        }
        if(!empty($options['height'])){
            $options['height']=$options['height']>600?600:$options['height'];
        }

        $largeImage->resize(new Box($options['width'],$options['height']));
        $largeFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_large.{$pathinfo['extension']}";
        $largeImage->save($largeFilePath, array('quality' => 100));
        $largeFileRecord = $this->getFileService()->uploadImgFile('photo', new File($largeFilePath));
        return $largeFileRecord['uri'];
    } 

    public function deleteFileAction(Request $request, $id)
    {
        $result = $this->getPhotoService()->deleteFile($id);
        return $this->createJsonResponse(true);
    }

    public function publishAction(Request $request, $id)
    {
        $this->getActivityService()->publishActivity($id);
        return $this->createJsonResponse(true);
    }


    public function picturesAction(Request $request,$id){
        $course = $this->getActivityService()->getActivity($id);
        return $this->render('TopxiaWebBundle:ActivityManage:pictures.html.twig', array(
            'course' => $course
        ));   
    }

    private function createPhotoBaseForm($course)
    {
        $builder = $this->createNamedFormBuilder('Photo', $course)
            ->add('name', 'text')
            ->add('tagIds', 'tags');

        return $builder->getForm();
    }


    public function headerAction($photo, $manage = false)
    {

        return $this->render('TopxiaWebBundle:PhotoView:header.html.twig', array(
            'photo' => $photo,
            'manage' => $manage,
        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getPhotoService()
    {
        return $this->getServiceKernel()->createService('Photo.PhotoService');
    }

    private function getActivityService(){
        return $this->getServiceKernel()->createService('Activity.ActivityService');
    }

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

}