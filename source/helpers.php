<?php
use Const424\Eclipse\View;
use Const424\Eclipse\Config;
use Const424\Eclipse\Request;
use Const424\Eclipse\Storage;
use Const424\Eclipse\Language;
use Illuminate\Database\Capsule\Manager as Eloquent;

function eloquent(array $config) {
	$db = new Eloquent;
	$db->addConnection($config);
	$db->setAsGlobal();
	$db->bootEloquent();
}

function config(string $key, $fallback = null) {
	return Config::get($key, $fallback);
}

function str_random(int $length = 10, string $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
	return substr(str_shuffle(str_repeat($characters, ceil($length / strlen($characters)) )), 1, $length);
}

function str_slug(string $string, string $separator = '-') {
	$flip = $separator == '-' ? '_' : '-';
	$string = preg_replace('!['.preg_quote($flip).']+!u', $separator, $string);
	$string = str_replace('@', $separator.'at'.$separator, $string);
	$string = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', mb_strtolower($string));
	$string = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $string);
	$string = trim($string, $separator);
	
	return empty($string) ? null : $string;
}

function str_initials(string $string) {
	return array_reduce(explode(' ', $string), function ($initials, $word) {
		return sprintf('%s%s', $initials, substr($word, 0, 1));
	}, '');
}

function number_shorten(int $number) {
	if ($number < 999) {
		return $number;
	} else if ($number > 999 && $number <= 9999) {
		$number = substr($number, 0, 1);
		
		return "{$number}K+";
	} else if ($number > 9999 && $number <= 99999) {
		$number = substr($number, 0, 2);
		
		return "{$number}K+";
	} else if ($number > 99999 && $number <= 999999) {
		$number = substr($number, 0, 3);
		
		return "{$number}K+";
	} else if ($number > 999999 && $number <= 9999999) {
		$number = substr($number, 0, 1);
		
		return $number.'M+';
	} else if ($number > 9999999 && $number <= 99999999) {
		$number = substr($number, 0, 2);
		
		return "{$number}M+";
	} else if ($number > 99999999 && $number <= 999999999) {
		$number = substr($number, 0, 3);
		
		return "{$number}M+";
	} else {
		return $number;
	}
}

function resize_image(string $file, int $width, int $height = null) {
	$height = $height ?? $width;
	$image = imagecreatefromstring(file_get_contents($file));
	$newImage = imagecreatetruecolor($width, $height);
	
	imagealphablending($newImage, false);
	imagesavealpha($newImage, true);
	imagefilledrectangle($newImage, 0, 0, $width, $height, imagecolorallocatealpha($newImage, 255, 255, 255, 127));
	imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
	
	return imagepng($newImage, $file);
}

function url(string $path) {
	$url = config('app.url');
	
	if (in_array($path, ['', '/'])) {
		$path = '';
	} else if (!str_starts_with($path, '/')) {
		$path = "/{$path}";
	}
	
	return "{$url}{$path}";
}

function storage_url(string $path) {
	return Storage::url($path);
}

function view(string $path, array $data = []) {
	return View::render($path, $data);
}

function trans(string $key, array $data = []) {
	return Language::get($key, $data);
}

function ip() {
	return Request::ip();
}

function is_valid_ip(string $ip) {
	return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}

function is_valid_email(string $email) {
	return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function abort(int $code, array|string $message = null) {
	return Request::abort($code, $message);
}

function redirect(string $location, array $flashes = []) {
	foreach ($flashes as $key => $value) {
		$_SESSION["Eclipse.Flash.{$key}"] = $value;
	}
	
	die(header("Location: {$location}"));
}

function back(array $flashes = []) {
	redirect($_SERVER['HTTP_REFERER'] ?? '/', $flashes);
}

function json(object|array $data) {
	header('Content-Type: application/json');
	die(json_encode((array) $data));
}

function success() {
	http_response_code(200);
	json(['success' => true]);
}

function fail() {
	http_response_code(400);
	json(['success' => false]);
}