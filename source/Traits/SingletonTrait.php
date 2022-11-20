<?php
namespace Const424\Eclipse\Traits;

trait SingletonTrait
{
	private static $instances = [];
	
	public static function getInstance()
	{
		$self = static::class;
		
		if (!isset(self::$instances[$self])) {
			self::$instances[$self] = new $self;
		}
		
		return self::$instances[$self];
	}
	
	protected static function hasInstance()
	{
		return isset(self::$instances[static::class]);
	}
}