<?php 

return array(
		
	/*
	|--------------------------------------------------------------------------
	| Query Builder classname
	|--------------------------------------------------------------------------
	|
	| By default, Backenator provide a base query builder. But, you can create you own
	| if your API doesn't have the same response data structure
	|
	*/
	'queryBuilder' => '\EllipseSynergie\Backenator\Query\BaseBuilder',
		
	/*
	|--------------------------------------------------------------------------
	| The default API URL
	|--------------------------------------------------------------------------
	|
	| The default base url of the API used for each request
	|
	*/
	'baseUrl' => 'http://api.example.com/',
		
	/*
	|--------------------------------------------------------------------------
	| Add automaticly the ID has first segment when updating or deleting a entry
	|--------------------------------------------------------------------------
	|
	| By the default, the ID of the entry will be added automaticly when you doing PUT or DELETE request
	| @example : DELETE http://api.example.com/users/1
	|
	*/
	'autoId' => true,
		
	/*
	|--------------------------------------------------------------------------
	| Log the query into the app/storage/logs folder
	|--------------------------------------------------------------------------
	|
	| By default, each request will be log into the logs folder
	|
	*/
	'log' => true,		
		
	/*
	|--------------------------------------------------------------------------
	| Curl option
	|--------------------------------------------------------------------------
	|
	| @see http://php.net/manual/en/function.curl-setopt.php
	|
	*/
	'options' => array(
		CURLOPT_TIMEOUT => 10
	)
);