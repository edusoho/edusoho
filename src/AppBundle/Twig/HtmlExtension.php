<?php

namespace AppBundle\Twig;

use Codeages\Biz\Framework\Context\Biz;

class HtmlExtension extends \Twig_Extension
{
    protected $scripts = array();

    protected $csses = array();

    protected $container;

    protected $biz;

    public function __construct($container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFunctions()
    {
        $options = array('is_safe' => array('html'));

        return array(
            new \Twig_SimpleFunction('select_options', array($this, 'selectOptions'), $options),
            new \Twig_SimpleFunction('radios', array($this, 'radios'), $options),
            new \Twig_SimpleFunction('cd_radios', array($this, 'cdRadios'), $options),
            new \Twig_SimpleFunction('checkboxs', array($this, 'checkboxs'), $options),
            new \Twig_SimpleFunction('field_value', array($this, 'fieldValue'), $options),
            new \Twig_SimpleFunction('countdown', array($this, 'countdown'), $options),
        );
    }

    /**
     * 这个不要使用，要废弃.
     */
    public function fieldValue($object, $key, $default = '')
    {
        $html = '';

        if (empty($object)) {
            return $default;
        }

        if (!is_array($object) || !isset($object[$key])) {
            return $default;
        }

        return $object[$key];
    }

    public function selectOptions($choices, $selected = null, $empty = null)
    {
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
            $name = htmlspecialchars($name);
            if ($selected == $value) {
                $html .= "<option value=\"{$value}\" selected=\"selected\">{$name}</option>";
            } else {
                $html .= "<option value=\"{$value}\">{$name}</option>";
            }
        }

        return $html;
    }

    public function radios($name, $choices, $checked = null, $disable = null)
    {
        $html = '';

        foreach ($choices as $value => $label) {
            if ($checked == $value) {
                $html .= "<label><input type=\"radio\" name=\"{$name}\" value=\"{$value}\" {$disable} checked=\"checked\"> {$label}</label>";
            } else {
                $html .= "<label><input type=\"radio\" name=\"{$name}\" value=\"{$value}\" {$disable}> {$label}</label>";
            }
        }

        return $html;
    }

    public function cdRadios($name, $choices, $checked = null, $disable = null)
    {
        $html = '';

        foreach ($choices as $value => $label) {
            if ($checked == $value) {
                $html .= "<label class=\"cd-radio checked {$disable}\"><input type=\"radio\" name=\"{$name}\" value=\"{$value}\" {$disable} checked=\"checked\" data-toggle=\"cd-radio\"> {$label}</label>";
            } else {
                $html .= "<label class=\"cd-radio {$disable}\"><input type=\"radio\" name=\"{$name}\" value=\"{$value}\" {$disable} data-toggle=\"cd-radio\"> {$label}</label>";
            }
        }

        return $html;
    }

    public function checkboxs($name, $choices, $checkeds = array())
    {
        $html = '';

        if (!is_array($checkeds)) {
            $checkeds = array($checkeds);
        }

        foreach ($choices as $value => $label) {
            if (in_array($value, $checkeds)) {
                $html .= "<label><input type=\"checkbox\" name=\"{$name}[]\" value=\"{$value}\" checked=\"checked\"> {$label}</label>";
            } else {
                $html .= "<label><input type=\"checkbox\" name=\"{$name}[]\" value=\"{$value}\"> {$label}</label>";
            }
        }

        return $html;
    }

    public function countdown($timestamp)
    {
        $countdown = $timestamp - time();
        $unit = '';
        $result = '';

        if ($countdown >= 86400) {
            $unit = $this->trans('site.date.day');
            $result = $countdown / 86400;
        } elseif ($countdown >= 3600) {
            $unit = $this->trans('site.date.hour');
            $result = $countdown / 3600;
        } else {
            $unit = $this->trans('site.date.minute');
            $result = $countdown / 60;
        }

        $result = intval($result + 0.5);

        return $result.$unit;
    }

    public function getName()
    {
        return 'topxia_html_twig';
    }

    private function trans($key, $parameters = array())
    {
        return $this->container->get('translator')->trans($key, $parameters);
    }
}
