<?php
namespace Const424\Eclipse;

class Config
{
	protected static string $directory;
	protected static array $cache = [];
	
	public static function setDirectory(string $directory)
	{
		self::$directory = $directory;
	}
	
	public static function get(string $key, $fallback = null)
	{
		$array = explode('.', $key);
		
		if (!isset($array[0])) {
			return null;
		}
		
		if (!isset(self::$cache[$array[0]])) {
			$file = self::$directory . "/{$array[0]}.php";
			self::$cache[$array[0]] = file_exists($file) ? require($file) : [];
		}
		
		$config = self::$cache[$array[0]];
		
		unset($array[0]);
		
		if (isset($array[1])) {
			foreach ($array as $value) {
				if (!isset($config[$value])) {
					return null;
				} else {
					$config = $config[$value];
				}
			}
		}
		
		return $config;
	}
}