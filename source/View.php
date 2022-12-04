<?php
namespace Const424\Eclipse;

use Exception;
use Twig\TwigFilter;
use Twig\Environment;
use Twig\TwigFunction;
use Const424\Eclipse\Request;
use Const424\Eclipse\Session;
use Const424\Eclipse\Storage;
use Const424\Eclipse\Language;
use Twig\Loader\FilesystemLoader;

class View
{
	protected static array $cache = [];
	public static string $cacheDirectory;
	protected static Environment $twig;
	
	public static function setCacheDirectory(string $directory)
	{
		self::$cacheDirectory = $directory;
	}
	
	public static function setTemplatesDirectory(string $directory)
	{
		if (isset(self::$cache[$directory])) {
			self::$twig = self::$cache[$directory];
		} else {
			$config = [];
			
			if (isset(self::$cacheDirectory)) {
				$failed = false;
				
				if (!is_dir(self::$cacheDirectory)) {
					try {
						mkdir(self::$cacheDirectory);
					} catch (Exception $exception) {
						$failed = true;
					}
				}
				
				if (!$failed) {
					$config['cache'] = self::$cacheDirectory;
				}
			}
			
			self::$twig = new Environment(new FilesystemLoader($directory), $config);
			
			self::addFunction(new TwigFunction('url', function(string $path) {
				return url($path);
			}));
			
			self::addFunction(new TwigFunction('storage_url', function(string $path) {
				return Storage::url($path);
			}));
			
			self::addFunction(new TwigFunction('flash', function(string $key, $fallback = null) {
				return Session::get("Eclipse.Flash.{$key}", $fallback);
			}));
			
			self::addFunction(new TwigFunction('cookie', function(string $key, $fallback = null) {
				return Cookie::get($key, $fallback);
			}));
			
			self::addFunction(new TwigFunction('request', function(string $key, $fallback = null) {
				return Request::get($key, $fallback);
			}));
			
			self::addFilter(new TwigFilter('trans', function(string $key, array $data = []) {
				return Language::get($key, $data);
			}));
			
			self::addFilter(new TwigFilter('number_shorten', function(int $number) {
				return number_shorten($number);
			}));
			
			self::addFilter(new TwigFilter('str_initials', function(string $string) {
				return str_initials($string);
			}));
			
			self::addFilter(new TwigFilter('str_slug', function(string $string) {
				return str_slug($string);
			}));
			
			self::$cache[$directory] = self::$twig;
		}
	}
	
	public static function addGlobal(string $key, $value)
	{
		self::$twig->addGlobal($key, $value);
	}
	
	public static function addFunction(TwigFunction $function)
	{
		self::$twig->addFunction($function);
	}
	
	public static function addFilter(TwigFilter $filter)
	{
		self::$twig->addFilter($filter);
	}
	
	public static function render(string $path, array $data = [])
	{
		$view = self::$twig->render($path, $data);
		
		foreach ($_SESSION as $key => $value) {
			if (str_starts_with($key, 'Eclipse.Flash.')) {
				unset($_SESSION[$key]);
			}
		}
		
		return $view;
	}
}