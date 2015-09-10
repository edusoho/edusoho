<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/10
 * Time: 10:13
 */

namespace Custom\WebBundle\Controller;


class BaseController extends \Topxia\WebBundle\Controller\BaseController
{
    public function checkId($id)
    {
        if($id <= 0){
            throw $this->createNotFoundException();
        }
    }
}