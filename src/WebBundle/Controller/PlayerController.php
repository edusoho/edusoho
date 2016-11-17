<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 11/11/2016
 * Time: 10:25
 */

namespace WebBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\File\UploadFileService;

class PlayerController extends  BaseController
{
    public function showAction(Request $request, $id, $content= array()){
        $file = $this->getUploadFileService()->getFullFile($id);
        if(empty($file)){
            throw $this->createNotFoundException('file not found');
        }

    }


    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }
}