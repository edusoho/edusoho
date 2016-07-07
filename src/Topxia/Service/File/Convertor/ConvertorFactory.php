<?php
namespace Topxia\Service\File\Convertor;


class ConvertorFactory
{

	public static function create($name, $cloudClient, $cloude_convertor_default)
    {
    	if(empty($name) || !in_array($name,array('HLSVideo','HLSEncryptedVideo','Audio','Document','Ppt'))) {
    		throw new \Exception("转码类型不存在");
    	}

    	$class = __NAMESPACE__.'\\'.ucfirst($name).'Convertor';

        return new $class($cloudClient, $cloude_convertor_default);
    }

}


