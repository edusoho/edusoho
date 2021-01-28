<?php

namespace Biz\Course\Util;

use AppBundle\Component\ViewResolver\RenderViewResolver;
use AppBundle\Common\DynUrlToolkit;

class CourseRenderViewResolver implements RenderViewResolver
{
    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function generateRenderView($view, array $parameters = array())
    {
        if (isset($parameters['course'])) {
            $type = $parameters['course']['type'];
        } elseif (isset($parameters['courseSet'])) {
            $type = $parameters['courseSet']['type'];
        } elseif (isset($parameters['params']['type'])) {
            $type = $parameters['params']['type'];
        } else {
            return $view;
        }

        return DynUrlToolkit::getUrl($this->biz, $view, array('type' => $type));
    }
}
