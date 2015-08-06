<?php
namespace Custom\WebBundle\Twig\Extension;

use Topxia\Service\Common\ServiceKernel;
use Topxia\WebBundle\Util\CategoryBuilder;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\NumberToolkit;
use Topxia\Common\ConvertIpToolkit;
use Topxia\Service\Util\HTMLPurifierFactory;
use Topxia\WebBundle\Util\UploadToken;
use Topxia\Common\ExtensionManager;

class WebExtension extends \Twig_Extension
{
    protected $container;

    protected $pageScripts;

    public function __construct ($container)
    {
        $this->container = $container;
    }

    public function getName ()
    {
        return 'custom_web_twig';
    }

    public function getFilters ()
    {
        return array(
            'num2chinese' => new \Twig_Filter_Method($this, 'num2chinese')
        );
    }

    public function getFunctions()
    {
        return array(
            
        );
    }

    function num2chinese($num){
        $char = array("零","一","二","三","四","五","六","七","八","九");
        $dw = array("","十","百","千","万","亿","兆");
        $retval = "";
        $proZero = false;
        for($i = 0;$i < strlen($num);$i++)     {         
            if($i > 0)
                $temp = (int)(($num % pow (10,$i+1)) / pow (10,$i));
            else 
                $temp = (int)($num % pow (10,1));
            
            if($proZero == true && $temp == 0) continue;
            
            if($temp == 0) 
                $proZero = true;
            else 
                $proZero = false;
            
            if($proZero) {
                if($retval == "") continue;
                $retval = $char[$temp].$retval;
            }else 
                $retval = $char[$temp].$dw[$i].$retval;
        }
        if(strpos($retval,"一十") ===0){
            $retval=preg_replace('/^一十/','十',$retval);
        }
        return $retval;

    }
}


