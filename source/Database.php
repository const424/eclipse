<?php
namespace Const424\Eclipse;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
	protected static Capsule $capsule;
	
	public static function getCapsule()
	{
		return self::$capsule;
	}
	
	public static function connect(array $config)
	{
		self::$capsule = new Capsule;
		self::$capsule->addConnection($config);
		self::$capsule->setAsGlobal();
		self::$capsule->bootEloquent();
	}
}