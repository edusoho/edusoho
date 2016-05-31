<?php

namespace Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\BaseParameterBag;


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
     
        while (list($key, $value) = each($parameters)){
            if (is_array($value)){
                $parameters[$key] = $this->trimArray($value);
            } else {
                $parameters[$key] = trim($value);
            }
        }
        return $parameters;
    }
}
