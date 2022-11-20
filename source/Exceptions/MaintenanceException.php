<?php
namespace Const424\Eclipse\Exceptions;

use Exception;
use Const424\Eclipse\View;

class MaintenanceException
{
	public function __construct(array $config)
	{
		if (IS_API) {
			throw new RouteException(503, $config['message']);
		}
		
		http_response_code(503);
		
		try {
			die(view('maintenance.twig', $config));
		} catch (Exception $exception) {
			View::setTemplatesDirectory(__DIR__ . '/../views');
			
			die(view('maintenance.twig', $config));
		}
	}
}