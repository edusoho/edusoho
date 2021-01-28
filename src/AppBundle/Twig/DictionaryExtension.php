<?php

namespace AppBundle\Twig;

use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DictionaryExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var Biz
     */
    protected $biz;

    protected $pageScripts;

    public function __construct($container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFunctions()
    {
        $options = array('is_safe' => array('html'));

        return array(
            new \Twig_SimpleFunction('dict_select_options', array($this, 'dictSelectOptions'), $options),
        );
    }

    public function dictSelectOptions($type, $selected = null, $empty = null)
    {
        $choices = $this->container->get('codeages_plugin.dict_twig_extension')->getDict($type);

        $html = '';

        if (!is_null($empty)) {
            if (is_array($empty)) {
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

    public function getName()
    {
        return 'topxia_dictionary_twig';
    }

    protected function getDictionaryService()
    {
        return $this->biz->service('Dictionary:DictionaryService');
    }
}
