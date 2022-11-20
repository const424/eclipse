<?php
namespace Const424\Eclipse;

use stdClass;
use Const424\Eclipse\Request;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
	public $paginator;
	
	protected $hidden = ['password', 'email', 'email_address', 'emailAddress', 'ip', 'ip_address', 'ipAddress'];
	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime'
	];
	
	public function scopeWithPagination($query, int $perPage, string $parameter = 'page') {
		$pages = ceil($query->count() / $perPage);
		$page = (int) Request::get($parameter, 1);
		
		if ($pages == 0) {
			$pages = 1;
		} if (!is_int($page)) {
			$page = 1;
		} else if ($page < 1) {
			$page = 1;
		} else if ($page > $pages) {
			$page = $pages;
		}
		
		$offset = ($page - 1) * $perPage;
		$paginator = new stdClass;
		$paginator->perPage = $perPage;
		$paginator->parameter = $parameter;
		$paginator->currentPage = $page;
		$paginator->totalPages = $pages;
		$paginator->hasPreviousPage = $page > 1;
		$paginator->hasNextPage = $page < $pages;
		$paginator->previousPage = $paginator->hasPreviousPage ? $page - 1 : 1;
		$paginator->nextPage = $paginator->hasNextPage ? $page + 1 : $pages;
		$results = $query->take($perPage)->skip(($page - 1) * $perPage)->get();
		$results->paginator = $paginator;
		
		return $results;
	}
}