<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/25
 * Time: 10:40
 */

namespace Custom\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\DefaultController as BaseDefaultController;

class DefaultController extends BaseDefaultController
{
    public function getCloudNoticesAction(Request $request)
    {
        $userAgent = 'Open EduSoho App Client 1.0';
        $connectTimeout = 10;
        $timeout = 10;
        $url = "http://open.edusoho.com/api/v1/context/moocNotice";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_URL, $url );
        $notices = curl_exec($curl);
        curl_close($curl);
        $notices = json_decode($notices, true);

        return $this->render('TopxiaAdminBundle:Default:cloud-notice.html.twig',array(
            "notices"=>$notices,
        ));
    }
}