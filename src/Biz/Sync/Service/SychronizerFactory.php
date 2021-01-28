<?php

namespace Biz\Sync\Service;

class SychronizerFactory
{
    /**
    * @param $alias
    *
    * @return AbstractSychronizer
    */
   public static function create($alias)
   {
       list($module, $className) = explode(':', $alias);
       $class = "Biz\\{$module}\\Sync\\{$className}";

       return new $class();
   }
}
