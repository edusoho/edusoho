<?php

namespace Topxia\Common;

use Symfony\Component\HttpFoundation\ParameterBag as BaseParameterBag;


class ParameterBag extends BaseParameterBag
{
    public function __construct(array $parameters = array())
    {
        $this->parameters = $this->trimArray($parameters);
    }

    protected function trimArray($parameters){
        if (!is_array($parameters)) { 
            return $parameters;
        }

        foreach($parameters as $key => $value) {
            if (is_array($value)){
                $parameters[$key] = $this->trimArray($value);
            } elseif (is_string($value)) {
                $parameters[$key] = trim($value);
            }
        }
        return $parameters;
    }
}
