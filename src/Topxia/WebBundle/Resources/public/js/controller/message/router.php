<?php
$router = new Framework\Router();
$router = addRoute(
	new Framework\Router\Route\Simple(array(
		'pattern' => ":name/profile" ,
		"controller"=>"index",
		"action" => "home"
		 ))
	);

$router->url = "chris/profile";
$router->dispatch();

namespace Framework\Router
{
	use Framework\Base as Base;
	use Framework\Router as Router;
	use Framework\Registry as Registry;
	use Framework\Inspector as Inspector;
	use Framework\Router\Exception as Exception;

	class Regex extends Router\Route
	{
		protected $keys;
		public function matches($url)
		{
			$pattern = $this->pattern;
			preg_match_all("#^{$pattern}$#", $url, $values);
			if (sizeof($values) && sizeof($values[0]) && sizeof($values[1]))
			{
				$derived = array_combine($this->keys, $values[1]);
				$this->parameters = array_merge($this->parameters, $derived);
				return true;
			}
			return false;
		}
	}

	class ArrayMethods 
	{
		public function flatten($array, $return = array())
		{
			foreach ($array as $key => $value) {
				if(is_array($value) || is_object($value)){
					$return = self::flatten($value, $return);
				} else {
					$return[] = $value;
				}
			}
			return $return;
		}
	}

	class Simple extends Router\Route
	{
		public function matches($url)
		{
			$pattern = $this->pattern;
			preg_match_all("#:([a-zA-Z0-9]+)#", $pattern, $keys);
			if(sizeof($keys)) && sizeof($keys[0] && sizeof($keys[1])){
				$keys = $keys[1];
			} else {
				return preg_match("#^{$pattern}$#", $url);
			}
			$pattern = preg_replace("#(:[a-zA-Z0-9]+)#", "([a-zA-Z0-9-]+)", $pattern);

			preg_match_all("#^{$pattern}$#",$url,$values);

			if(sizeof($values) && sizeof($values[0]) && sizeof($values[1])){
				unset($values[0]);
				$derived = array_combine($keys, ArrayMethods::flatten($values));
				$this->parameters = array_merge($this->parameters, $derived);
				return true;
			}

		}
	}

	class Router extends Base 
	{
		protected $url;
		protected $extension;
		protected $controller;
		protected $action;
		protected $routes = array();
		public function getExceptionForImplementation($method)
		{
			return new Exception\Implementation("{$method} method not implemented");
		}

		public function addRoute($route)
		{
			$this->routes[] = $route;
			return $this;
		}
		
		public function removeRoute($route)
		{
			foreach ($this->routes as $i => $stored) {
				if($stored == $route){
					unset($this->routes[$i]);
				}
			}
			return $this;
		}

		public function getRoutes()
		{
			$list = array();
			foreach ($this->routes as $route) {
				$list[$route->pattern] = get_class($route);
			}
			return $list;
		}

		public function dispatch()
		{
			$url = $this->url;
			$parameters = array();
			$controller = "index";
			$action = "index";
			foreach ($this->routes as $route) {
				$matches = $route->matches($url);
				if($matches){
					$controller = $route->controller;
					$action = $route->action;
					$parameters = $route->parameters;
					$this->pass($controller, $action, $parameters);
					return;
				}
			}
		}

		public function dispath()
		{
			$parts = explode("/", trim($url, "/"));

			if(sizeof($parts) > 0){
				$controller = $parts[0];
				if(sizeof($parts)>= 2){
					$action = $parts[1];
					$parameters = array_slice($parts, 2);
				}
			}
			$this->pass($controller, $action, $parameters);
		}


		protected function pass($controller, $action, $parameters = array())
		{
			$name = ucfirst($controller);
			$this->controller = $controller;
			$this->action = $action;

			try{
				$instance = new $name(array(
					"parameters"=>$parameters
				));
				Registry::set("controller", $instance);
			} catch (\Exception $e){
				throw new Exception\Controller("Controller {$name} not found", 1);
			}

			if(!method_exists($instance, $action)){
				$instance->willRenderLayoutView = false;
				$instance->willRenderActionView = false;
				throw new Exception\Action("Action {$action } not found", 1);
			}

			$inspector = new Inspector($instance);

		}
	}
}

