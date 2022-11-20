<?php
namespace Const424\Eclipse;

class Session
{
	public static function exists(string $key)
	{
		return isset($_SESSION[$key]);
	}
	
	public static function all()
	{
		return $_SESSION;
	}
	
	public static function get(string $key, $fallback = null)
	{
		return $_SESSION[$key] ?? $fallback;
	}
	
	public static function flush()
	{
		session_destroy();
	}
	
	public static function set(array|string $key, $value = null)
	{
		if (!is_array($key)) {
			$_SESSION[$key] = $value;
		} else {
			foreach ($key as $session => $value) {
				$_SESSION[$session] = $value;
			}
		}
	}
	
	public static function delete()
	{
		$sessions = func_get_args();
		
		foreach ($sessions as $session) {
			if (isset($_SESSION[$session])) {
				unset($_SESSION[$session]);
			}
		}
	}
	
	public static function flash(array|string $key, $value = null)
	{
		if (!is_array($key) && !$value) {
			return $this->get("Eclipse.Flash.{$key}");
		} else if (!is_array($key)) {
			$this->set("Eclipse.Flash.{$key}", $value);
		} else {
			foreach ($key as $session => $value) {
				$this->set("Eclipse.Flash.{$session}", $value);
			}
		}
	}
}