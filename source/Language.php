<?php
namespace Const424\Eclipse;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;

class Language
{
	protected static string $locale;
	protected static FileLoader $loader;
	public static Translator $translator;
	
	public static function getLocale()
	{
		return self::$locale;
	}
	
	public static function setLocale(string $locale)
	{
		self::$locale = $locale;
		self::$translator = new Translator(self::$loader, $locale);
	}
	
	public static function setDirectory(string $directory)
	{
		self::$loader = new FileLoader(new Filesystem, $directory);
		self::$loader->addNamespace('language', $directory);
	}
	
	public static function get(string $key, array $data = [])
	{
		return self::$translator->get($key, $data);
	}
}