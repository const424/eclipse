<?php
namespace Const424\Eclipse;

use Const424\Eclipse\Database;
use Const424\Eclipse\Language;
use Const424\Eclipse\Validator;
use Illuminate\Validation\Factory;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Const424\Eclipse\Exceptions\RouteException;
use Illuminate\Validation\DatabasePresenceVerifier;

class Request
{
	protected static ?array $json = null;
	
	public static function all()
	{
		return (object) $_REQUEST;
	}
	
	public static function ip()
	{
		return $_SERVER['REMOTE_ADDR'];
	}
	
	public static function agent()
	{
		return $_SERVER['HTTP_USER_AGENT'] ?? null;
	}
	
	public static function method()
	{
		return $_SERVER['REQUEST_METHOD'] ?? null;
	}
	
	public static function referer()
	{
		return $_SERVER['HTTP_REFERER'] ?? null;
	}
	
	public static function path()
	{
		return strtolower($_SERVER['REQUEST_URI'] ?? '');
	}
	
	public static function url()
	{
		return !IS_CLI ? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}" : null;
	}
	
	public static function json(string $key, $fallback = null)
	{
		if (is_null(self::$json)) {
			self::$json = json_decode(file_get_contents('php://input'), true);
		}
		
		return self::$json->$key ?? $fallback;
	}
	
	public static function get(string $key, $fallback = null)
	{
		return $_REQUEST[$key] ?? $fallback;
	}
	
	public static function has(string $key)
	{
		return isset($_REQUEST[$key]);
	}
	
	public static function header(string $key, $fallback = null)
	{
		return $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $key))] ?? $fallback;
	}
	
	public static function hasHeader(string $key)
	{
		return isset($_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $key))]);
	}
	
	public static function file(string $key)
	{
		if (self::hasFile($key)) {
			return (object) $_FILES[$key];
		}
	}
	
	public static function hasFile(string $key)
	{
		return isset($_FILES[$key]);
	}
	
	public static function set(array|string $key, $value = null)
	{
		if (!is_array($key)) {
			$_REQUEST[$key] = $value;
		} else {
			foreach ($key as $request => $value) {
				$_REQUEST[$request] = $value;
			}
		}
	}
	
	public static function abort(int $code, array|string $message = null)
	{
		throw new RouteException($code, $message);
	}
	
	public static function validate(array $rules, array $messages = [])
	{
		$validator = new Factory(Language::$translator);
		$validator->setPresenceVerifier(new DatabasePresenceVerifier(Database::getCapsule()->getDatabaseManager()));
		$request = SymfonyRequest::createFromGlobals();
		
		return $validator->make($_REQUEST + $request->files->all(), $rules, $messages);
	}
}