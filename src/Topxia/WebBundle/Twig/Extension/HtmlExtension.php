<?php
namespace Topxia\WebBundle\Twig\Extension;

class HtmlExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        $options = array('is_safe' => array('html'));
        return array(
            new \Twig_SimpleFunction('select_options', array($this, 'selectOptions'), $options),
            new \Twig_SimpleFunction('radios', array($this, 'radios'), $options),
            new \Twig_SimpleFunction('checkboxs', array($this, 'checkboxs'), $options),
            new \Twig_SimpleFunction('field_value', array($this, 'fieldValue'), $options),
            new \Twig_SimpleFunction('countdown', array($this, 'countdown'), $options)
        );
    }

    /**
     * 这个不要使用，要废弃
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
                $html .= "<option value=\"0\">{$empty}</option>";
            }
        }

        foreach ($choices as $value => $name) {
            if ($selected == $value) {
                $html .= "<option value=\"{$value}\" selected=\"selected\">{$name}</option>";
            } else {
                $html .= "<option value=\"{$value}\">{$name}</option>";
            }
        }

        return $html;
    }

    public function radios($name, $choices, $checked = null)
    {
        $html = '';

        foreach ($choices as $value => $label) {
            if ($checked == $value) {
                $html .= "<label><input type=\"radio\" name=\"{$name}\" value=\"{$value}\" checked=\"checked\"> {$label}</label>";
            } else {
                $html .= "<label><input type=\"radio\" name=\"{$name}\" value=\"{$value}\"> {$label}</label>";
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
        $unit      = '';
        $result    = '';

        if ($countdown >= 86400) {
            $unit   = '天';
            $result = $countdown / 86400;
        } elseif ($countdown >= 3600) {
            $unit   = '小时';
            $result = $countdown / 3600;
        } else {
            $unit   = '分钟';
            $result = $countdown / 60;
        }

        $result = intval($result + 0.5);
        return $result.$unit;
    }

    public function getName()
    {
        return 'topxia_html_twig';
    }
}
