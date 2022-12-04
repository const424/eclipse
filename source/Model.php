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
	
	public function scopeWithPagination($query, int $perPage, string $parameter = 'page')
	{
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
		$paginator->per_page = $perPage;
		$paginator->parameter = $parameter;
		$paginator->current_page = $page;
		$paginator->total_pages = $pages;
		$paginator->has_previous_page = $page > 1;
		$paginator->has_next_page = $page < $pages;
		$paginator->previous_page = $paginator->has_previous_page ? $page - 1 : 1;
		$paginator->next_page = $paginator->has_next_page ? $page + 1 : $pages;
		$results = $query->take($perPage)->skip(($page - 1) * $perPage)->get();
		$results->paginator = $paginator;
		
		return $results;
	}
}