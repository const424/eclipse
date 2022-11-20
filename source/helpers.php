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

function str_rand(int $length = 10, string $characters = '0123456789abcdefghijklmnopqrstuvwxyz') {
	return substr(str_shuffle(str_repeat($characters, ceil($length / strlen($characters)) )), 1, $length);
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