<?php
namespace Const424\Eclipse;

class Cookie
{
	public static function all()
	{
		return $_COOKIE;
	}
	
	public static function exists(string $key)
	{
		return isset($_COOKIE[$key]);
	}
	
	public static function get(string $key, $fallback = null)
	{
		return $_COOKIE[$key] ?? $fallback;
	}
	
	public static function set(string $key, $value, int $expiration = (365 * 24 * 60 * 60))
	{
		setcookie($key, $value, time() + $expiration, '/');
		$_COOKIE[$key] = $value;
	}
	
	public static function delete()
	{
		$cookies = func_get_args();
		
		foreach ($cookies as $cookie) {
			if ($this->exists($cookie)) {
				setcookie($cookie, '', time() - 3600);
				unset($_COOKIE[$cookie]);
			}
		}
	}
}