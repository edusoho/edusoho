<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\KeywordFilter;

class SensitiveWordController extends BaseController
{
	public function indexAction(Request $request)
    {   

    	$sensitiveWordSetting = $this->getSettingService()->get("sensitiveWord", array());
    	if($request->getMethod() == 'POST'){
    		$fields = $request->request->all();
    		$sensitiveWordSetting = ArrayToolkit::parts($fields, array("enabled", "ignoreWord", "wordReplace", "firstLevel", "secondLevel"));

            $keywords = explode("\r\n", $sensitiveWordSetting["secondLevel"]);

            $keywordFilter = new KeywordFilter();
            $keywordFilter->addKeywords($keywords);

    		$this->getSettingService()->set("sensitiveWord", $sensitiveWordSetting);

    	}

        return $this->render('TopxiaAdminBundle:SensitiveWord:index.html.twig', array(
        	"sensitiveWordSetting" => $sensitiveWordSetting
        ));
    }


    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}