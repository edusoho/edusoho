<?php

namespace AppBundle\Component\ViewResolver;

interface RenderViewResolver
{
    public function generateRenderView($view, array $parameters = array());
}
