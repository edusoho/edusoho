<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/9
 * Time: 15:42
 */

namespace Custom\AdminBundle\Controller;


class BaseController extends \Topxia\AdminBundle\Controller\BaseController
{
    protected function checkId($id){
        if($id <= 0){
            throw $this->createNotFoundException();
        }
    }
}