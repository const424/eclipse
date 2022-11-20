<?php
namespace Const424\Eclipse\Exceptions;

use Exception;
use Const424\Eclipse\View;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RouteException extends Exception
{
	protected $code;
	protected $message;
	protected $exception;
	protected array $data;
	protected static string $errorHandler;
	
	public function __construct(int $code, array|string $message = null, $exception = null)
	{
		$this->code = $code;
		$this->message = $message;
		$this->exception = $exception;
		$this->data = $exception && IS_DEBUG ? [
			'exception' => [
				'message' => $exception->getMessage(),
				'class' => get_class($exception),
				'trace' => $exception->getTraceAsString(),
				'file' => $exception->getFile(),
				'line' => $exception->getLine()
			]
		] : [];
		
		if ($exception) {
			if ($exception instanceof ModelNotFoundException) {
				$this->code = 404;
			} else if ($exception instanceof QueryException && str_contains($exception->getMessage(), 'No connection could be made')) {
				$this->code = 503;
			}
		}
		
		if ($exception && $exception instanceof ModelNotFoundException) {
			$this->code = 404;
		}
		
		$this->handle();
	}
	
	public static function setErrorHandler(string $class)
	{
		self::$errorHandler = $class;
	}
	
	private function handle()
	{
		http_response_code($this->code);
		
		switch ($this->code) {
			case 400:
				$title = 'Bad Request';
				$_message = IS_API
					? 'Bad request.'
					: 'Your browser has sent a request that we can not process.';
				break;
			case 401:
				$title = 'Unauthorized';
				$_message = IS_API
					? 'Unauthorized.'
					: 'You are not authorized to access the file or resource requested.';
				break;
			case 403:
				$title = 'Forbidden';
				$_message = IS_API
					? 'Forbidden.'
					: 'You do not have permission to access the file or resource requested.';
				break;
			case 404:
				$title = 'Page Not Found';
				$_message = IS_API
					? ($this->exception && $this->exception instanceof ModelNotFoundException ? 'Resource not found.' : 'Page not found.')
					: 'The page or resource you have requested could not be found or never existed.';
				break;
			case 405:
				$title = 'Method Not Allowed';
				$_message = IS_API
					? 'Method not allowed.'
					: 'This method is not allowed.';
				break;
			case 422:
				$title = 'Unprocessable Request';
				$_message = 'We were unable to process your request.';
				break;
			case 429:
				$title = 'Too Many Requests';
				$_message = 'You are sending too many requests.';
				break;
			case 440:
				$title = 'Session Expired';
				$_message = 'Session expired.';
				break;
			case 500:
				$title = 'Internal Server Error';
				$_message = IS_API
					? 'Internal server error.'
					: 'Oops! Looks like something is going wrong on our end. Press back and try again in a few moments.';
				break;
			case 503:
				$title = 'Service Unavailable';
				$_message = IS_API
					? 'Service unavailable.'
					: 'We are currently performing maintenance. Please try again later.';
				break;
			default:
				$this->code = 400;
				$title = 'Unknown Error';
				$_message = 'An unknown error has occurred.';
		}
		
		if (!$this->message) {
			$this->message = $_message;
		} if (!is_array($this->message)) {
			$this->message = [$this->message];
		}
		
		if (isset($this::$errorHandler) && class_exists($this::$errorHandler)) {
			$middleware = new $this::$errorHandler;
			
			if (method_exists($middleware, 'handle')) {
				die($middleware->handle($this->code, $title, $this->message, $this->data, $this->exception));
			}
		}
		
		if (IS_API) {
			json([
				'success' => false,
				'messages' => $this->message,
				... $this->data
			]);
		} else {
			View::setTemplatesDirectory(__DIR__ . '/../views');
			die(view('error.twig', [
				'title' => $title,
				'messages' => $this->message,
				'code' => $this->code,
				'referer' => $_SERVER['HTTP_REFERER'] ?? null,
				... $this->data
			]));
		}
	}
}