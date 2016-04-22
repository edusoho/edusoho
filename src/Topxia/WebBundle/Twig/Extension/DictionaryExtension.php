<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Service\Common\ServiceKernel;

class DictionaryExtension extends \Twig_Extension
{

    public function getFunctions()
    {
        $options = array('is_safe' => array('html'));
        return array(
            new \Twig_SimpleFunction('dict_select_options', array($this, 'dictSelectOptions'), $options),
        );
    }

    public function dictSelectOptions($type, $selected = null, $empty = null)
    {
        $dictionaryItems = $this->getDictionaryService()->findDictionaryItemByType($type);
        if ($type == 'refund_reason') {
            $choices['reason'] = '--请选择退学原因--';
            $selected = 'reason';
        }
            
        foreach ($dictionaryItems as $key => $value) {
            $choices[$key] = $value['name'];
        }
        $choices['other'] = '其他';

        $html = '';
        if (!is_null($empty)) {
            if(is_array($empty)){
                foreach ($empty as $key => $value) {
                    $html .= "<option value=\"{$key}\">{$value}</option>";
                }
            } else {
                $html .= "<option value=\"\">{$empty}</option>";
            }

        }

        foreach ($choices as $value => $name) {
            if ($selected === $value) {
                $html .= "<option value=\"{$value}\" selected=\"selected\">{$name}</option>";
            } else {
                $html .= "<option value=\"{$value}\">{$name}</option>";
            }
        }

        return $html;
    }

    public function getName ()
    {
        return 'topxia_dictionary_twig';
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function getDictionaryService()
    {
        return $this->getServiceKernel()->createService('Dictionary.DictionaryService');
    }
}