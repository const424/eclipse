<?php
namespace Const424\Eclipse;

use Exception;

class Maintenance
{
	public const SESSION_KEY = 'Eclipse.MaintenanceKey';
	public const DEFAULT_MESSAGE = 'We are currently performing maintenance. Please try again later.';
	
	protected static object $config;
	protected static string $file;
	
	public static function setFile(string $file)
	{
		self::$file = $file;
	}
	
	public static function loadConfig()
	{
		$default = [
			'enabled' => false,
			'message' => self::DEFAULT_MESSAGE,
			'keys' => [],
			'exceptions' => []
		];
		
		self::$config = file_exists(self::$file) ? json_decode(file_get_contents(self::$file)) : (object) $default;
		self::$config->enabled = self::$config->enabled ?? $default['enabled'];
		self::$config->message = self::$config->message ?? $default['message'];
		self::$config->keys = self::$config->keys ?? $default['keys'];
		self::$config = (object) self::$config;
	}
	
	public static function enabled()
	{
		return file_exists(self::$file) && self::$config->enabled;
	}
	
	public static function loggedIn()
	{
		return isset($_SESSION[self::SESSION_KEY]) && in_array($_SESSION[self::SESSION_KEY], self::$config->keys);
	}
	
	public static function config()
	{
		return self::$config;
	}
	
	public static function login(string $key)
	{
		$_SESSION[self::SESSION_KEY] = $key;
	}
	
	public static function logout()
	{
		unset($_SESSION[self::SESSION_KEY]);
	}
	
	public static function enable()
	{
		$config = self::$config;
		$config->enabled = true;
		
		return self::updateConfig($config);
	}
	
	public static function disable()
	{
		$config = self::$config;
		$config->enabled = false;
		
		return self::updateConfig($config);
	}
	
	public static function setMessage(string $message)
	{
		$config = self::$config;
		$config->message = $message;
		
		return self::updateConfig($config);
	}
	
	public static function resetMessage()
	{
		return self::setMessage(self::DEFAULT_MESSAGE);
	}
	
	public static function isValidKey(string $key)
	{
		return in_array($key, self::$config->keys);
	}
	
	public static function addKey(string $key)
	{
		$config = self::$config;
		
		if (!in_array($key, $config->keys)) {
			$config->keys[] = $key;
		}
		
		return self::updateConfig($config);
	}
	
	public static function deleteKey(string $key)
	{
		$config = self::$config;
		$keys = [];
		
		for ($i = 0; $i < count($config->keys); $i++) {
			if ($config->keys[$i] != $key) {
				$keys[] = $config->keys[$i];
			}
		}
		
		$config->keys = $keys;
		
		return self::updateConfig($config);
	}
	
	public static function isException(string $path = null)
	{
		$path = strtolower($path ?? $_SERVER['REQUEST_URI']);
		
		return in_array($path, self::$config->exceptions);
	}
	
	public static function addException(string $key)
	{
		$config = self::$config;
		
		if (!in_array($key, $config->exceptions)) {
			$config->exceptions[] = $key;
		}
		
		return self::updateConfig($config);
	}
	
	public static function deleteException(string $key)
	{
		$config = self::$config;
		$exceptions = [];
		
		for ($i = 0; $i < count($config->exceptions); $i++) {
			if ($config->exceptions[$i] != $key) {
				$exceptions[] = $config->exceptions[$i];
			}
		}
		
		$config->exceptions = $exceptions;
		
		return self::updateConfig($config);
	}
	
	protected static function updateConfig(array|object $config)
	{
		try {
			file_put_contents(self::$file, json_encode((array) $config, JSON_PRETTY_PRINT));
		} catch (Exception $exception) {
			return false;
		}
		
		self::$config = $config;
		
		return true;
	}
}