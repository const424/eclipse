<?php
namespace Const424\Eclipse;

use Exception;
use Dotenv\Dotenv;
use Const424\Eclipse\Cache;
use Const424\Eclipse\Email;
use Const424\Eclipse\Language;
use Const424\Eclipse\Maintenance;
use Const424\Eclipse\Exceptions\RouteException;

class Framework
{
	public static function start()
	{
		define('IS_CLI', php_sapi_name() == 'cli');
		
		header('X-Powered-By: Eclipse');
		header('X-Frame-Options: SAMEORIGIN');
		header('X-XSS-Protection: 1; mode=block');
		
		if (!defined('ROOT_DIR')) {
			http_response_code(500);
			die('ROOT_DIR is not set.');
		} else if (!defined('APP_DIR')) {
			http_response_code(500);
			die('APP_DIR is not set.');
		}
		
		self::getEnvironment();
		self::setServerVariables();
		self::defineGlobals();
		
		date_default_timezone_set($_SERVER['TIMEZONE']);
		
		if (!IS_CLI && session_status() != PHP_SESSION_ACTIVE) {
			session_name(env('SESSION_NAME', str_replace(' ', '_', strtolower(env('APP_NAME', 'Eclipse'))) . '-session'));
			session_start();
		}
		
		self::setErrorHandler();
		self::loadHelperFunctions();
	}
	
	private static function getEnvironment()
	{
		try {
			Dotenv::createImmutable(ROOT_DIR)->load();
		} catch (Exception $exception) {
			throw new Exception('Unable to read environment file.');
		}
	}
	
	private static function setServerVariables()
	{
		$_SERVER['TIMEZONE'] = env('TIMEZONE', 'UTC');
		$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
		$_SERVER['REQUEST_URI'] = !IS_CLI ? (!empty(rtrim($_SERVER['REQUEST_URI'], '/')) ? rtrim($_SERVER['REQUEST_URI'], '/') : $_SERVER['REQUEST_URI']) : null;
		$_SERVER['REQUEST_API'] = !IS_CLI ? (explode('/', strtolower($_SERVER['REQUEST_URI']))[1] === 'api' || env('API') == 'true') : false;
	}
	
	private static function defineGlobals()
	{
		define('IS_DEBUG', env('DEBUG') == 'true');
		define('IS_API', $_SERVER['REQUEST_API']);
	}
	
	private static function setErrorHandler()
	{
		ini_set('display_errors', IS_DEBUG);
		error_reporting(IS_DEBUG ? E_ALL : false);
		set_exception_handler(function($exception)
		{
			if (IS_CLI) {
				die($exception);
			}
			
			$code = $exception->getCode();
			
			if ($code <= 0 || !is_numeric($code)) {
				$code = 500;
			}
			
			throw new RouteException($code, null, $exception);
		});
	}
	
	private static function loadHelperFunctions()
	{
		include_once __DIR__ . '/helpers.php';
	}
}