<?php
namespace Const424\Eclipse;

use FastRoute;
use Const424\Eclipse\Exceptions\RouteException;

class Router
{
	protected string $namespace;
	protected string $directory;
	protected array $routers = [];
	
	public function __construct(string $namespace)
	{
		$this->namespace = str_replace('/', '\\', $namespace);
	}
	
	public function setDirectory(string $directory)
	{
		$this->directory = $directory;
	}
	
	public function add(string $prefix, string $file)
	{
		$this->routers[$prefix] = "{$this->directory}/{$file}.php";
	}
	
	public function handle()
	{
		$routers = $this->routers;
		$uri = $_SERVER['REQUEST_URI'] == '' ? '/' : $_SERVER['REQUEST_URI'];
		$dispatcher = FastRoute\simpleDispatcher(function($router) use($routers) {
			foreach ($routers as $prefix => $file) {
				if (file_exists($file)) {
					$router->addGroup($prefix == '/' ? '' : $prefix, function($router) use ($file) {
						require $file;
					});
				}
			}
		});
		
		if (false !== $pos = strpos($uri, '?')) {
			$uri = substr($uri, 0, $pos);
		}
		
		$uri = rawurldecode(strtolower($uri));
		$response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $uri);
		
		if ($response[0] == FastRoute\Dispatcher::NOT_FOUND) {
			$response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $uri . '/');
		}
		
		if ($response[0] != FastRoute\Dispatcher::FOUND) {
			throw new RouteException(match($response[0]) {
				FastRoute\Dispatcher::NOT_FOUND => 404,
				FastRoute\Dispatcher::METHOD_NOT_ALLOWED => 405,
				default => 400
			});
		} else {
			$handler = $response[1];
			$variables = $response[2];
			list($class, $method) = explode('@', $handler, 2);
			$class = str_replace('/', '\\', "{$this->namespace}\\{$class}");
			
			if (!class_exists($class)) {
				throw new RouteException(404);
			}
			
			$controller = new $class;
			
			if (!method_exists($controller, $method)) {
				throw new RouteException(404);
			}
			
			foreach ($variables as $key => $value) {
				$_REQUEST[$key] = $value;
			}
			
			$output = $controller->$method(... array_values($variables));
			
			if (!is_array($output) && !is_object($output)) {
				echo $output;
			} else {
				header('Content-Type: application/json');
				echo json_encode((array) $output);
			}
		}
	}
}