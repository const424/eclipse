<?php
namespace Const424\Eclipse;

class Cache
{
	protected static bool $enabled = false;
	protected static $client;
	
	public static function connect(string $host, int $port, string $prefix = '')
	{
		if (class_exists('Memcached')) {
			$config = config('cache');
			
			self::$enabled = true;
			self::$client = new Memcached;
			self::$client->addServer($config['host'], $config['port']);
			self::$client->setOption(Memcached::OPT_PREFIX_KEY, $config['prefix']);
		} else {
			self::$client = new class {
				public function get($key) {}
				public function getAllKeys() {}
				public function set($key, $value, $expiry) {}
				public function delete($key) {}
				public function add($key, $value, $expiry) {}
				public function increment($key, $offset, $initialValue, $expiry) {}
				public function decrement($key, $offset, $initialValue, $expiry) {}
			};
		}
	}
	
	public static function isEnabled()
	{
		return self::$enabled;
	}
	
	public static function get(string $key, $callback = null)
	{
		return self::$client->get($key) ?? (is_callable($callback) ? $callback() : $callback);
	}
	
	public static function getAllKeys()
	{
		return self::$client->getAllKeys();
	}
	
	public static function set(string $key, $value, int $expiry = 10800)
	{
		return self::$client->set($key, $value, $expiry);
	}
	
	public static function delete(string $key)
	{
		return self::$client->delete($key);
	}
	
	public static function add(string $key, $value, int $expiry = 10800)
	{
		if (!self::get($key)) {
			return self::set($key, $value, $expiry);
		}
	}
	
	public static function increment(string $key, int $offset = 1, int $initialValue = 0, int $expiry = 0)
	{
		return self::$client->increment($key, $offset, $initialValue, $expiry);
	}
	
	public static function decrement(string $key, int $offset = 1, int $initialValue = 0, int $expiry = 0)
	{
		return self::$client->decrement($key, $offset, $initialValue, $expiry);
	}
}