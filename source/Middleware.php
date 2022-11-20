<?php
namespace Const424\Eclipse;

class Middleware
{
	protected static string $namespace;
	
	public static function setNamespace(string $namespace)
	{
		self::$namespace = str_replace('/', '\\', $namespace);
	}
	
	public static function run()
	{
		$middlewares = func_get_args();
		$namespace = self::$namespace;
		
		foreach ($middlewares as $middleware) {
			$class = "{$namespace}\\{$middleware}";
			
			if (class_exists($class)) {
				$middleware = new $class;
				
				if (method_exists($middleware, 'handle')) {
					$middleware->handle();
				}
			}
		}
	}
}