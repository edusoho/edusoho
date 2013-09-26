<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\TimeUtils;
use Topxia\WebBundle\Form\ActivityMemberType;


class PhotoViewController extends BaseController
{

	public function indexAction(Request $request){

        $filters = $this->getFileSearchFilters($request);
        $conditions = $this->convertFiltersToConditions($filters);
        $photolists = $this->getPhotoService()->searchPhotos($conditions,'latest', 0,100);
        $fileids=ArrayToolkit::column($photolists,'id');
        
        $photofile=$this->getPhotoService()->findFileByIds($fileids);
        
        $layoutdata=$this->convertLayoutArray($photofile);

        $photos = $this->getPhotoService()->searchPhotos(array(),'latest', 0,100);

        $castphotos=array();
        foreach ($photos as $value) {
            $castphotos[$value['id']]=$value;
        }


        $tags = $this->getTagService()->findAllTags(0, 100);

		return $this->render('TopxiaWebBundle:PhotoView:base.html.twig',array(
            'photos'=>$castphotos,
            'tags'=>$tags,
            'photofile'=>$photofile,
            'layoutdata'=> $layoutdata));
	}

	
	public function contentAction(Request $request,$id){

        $photofile=$this->getPhotoService()->getPhotoFile($id);
        $url=$this->getWebExtension()->getFilePath($photofile['url']);
        $photofile['url']=$url;
		return $this->createJsonResponse($photofile);
	}


	public function createAction(Request $request){

		 $form = $this->createPhotoForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $course = $form->getData();
                $course = $this->getPhotoService()->createPhoto($course);
                return $this->redirect($this->generateUrl('photoview_manage', array('id' => $course['id'])));
            }
        }

        return $this->render('TopxiaWebBundle:PhotoView:create.html.twig', array(
            'form' => $form->createView()
        ));	
	}

   	private function createPhotoForm()
    {
        return $this->createNamedFormBuilder('photo')
            ->add('name', 'text')
            ->getForm();
    }

     private function getFileSearchFilters($request)
    {
        $filters = array();

        $filters['tagId'] = $request->query->get('tagId');
    
        $filters['id'] = $request->query->get('id');
        
        return $filters;
    }

    private function convertFiltersToConditions($filters)
    {
        $conditions = array();
        if(!empty($filters['tagId'])){
            $conditions['tagId']=$filters['tagId'];
        }
        if(!empty($filters['id'])){
            $conditions['id']=$filters['id'];
        }
        return $conditions;
    }

    private function convertLayoutArray(array $files){
        $rootarray=array();
        $count=count($files)%3>0?(int)(count($files)/3)+1:(int)(count($files)/3);
        array_unshift($files,array());
        for($i =0; $i<$count;$i++){
            $newarray=array();
            for($j=0;$j<3;$j++){
                $obj=next($files);
                if($obj!=false){
                    $newarray[]=current($files);    
                }
            }
            $rootarray[]=$newarray;
        }
        return $rootarray;
    }




    private function getPhotoService()
    {
        return $this->getServiceKernel()->createService('Photo.PhotoService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
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



