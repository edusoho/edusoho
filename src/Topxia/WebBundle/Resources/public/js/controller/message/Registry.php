<?php

namespace Framework
{
	class Registry
	{
		private static $instances = array();

		private function __construct()
		{
			echo "__construct";
		}

		private function __clone()
		{
			echo "__clone";
		}

		public static function get($key, $default = null)
		{
			if(isset(self::$instances[$key]))
			{
				return self::$instances[$key];
			}
			return $default;
		}

		public static function set($key, $instance = null)
		{
			self::$instances[$key] = $instance;
 		}

 		public static function erase($key)
 		{
 			unset(self::$instances[$key]);
 		}
	}

	
}

Framework\Registry::set("ford", new Ford());
$car = new Car();
$car->setColor("Blue")->setProducer(Framework\Registry::get("ford"));
echo Framework\Registry::get("ford")->produces($car);
echo Framework\Registry::get("ford")->founder;
