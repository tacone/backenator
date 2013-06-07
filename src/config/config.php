<?php 

return array(
		
	/*
	|--------------------------------------------------------------------------
	| Query Builder classname
	|--------------------------------------------------------------------------
	|
	|
	*/
	'queryBuilder' => '\EllipseSynergie\Backenator\Query\BaseBuilder',
		
	/*
	|--------------------------------------------------------------------------
	| The default API URL
	|--------------------------------------------------------------------------
	|
	|
	*/
	'baseUrl' => 'http://api.als11.ellipsesynergie.loc/',
		
	/*
	|--------------------------------------------------------------------------
	| Add automaticly the ID has first segment when updating or deleting a entry
	|--------------------------------------------------------------------------
	|
	|
	*/
	'autoId' => true,
		
	/*
	|--------------------------------------------------------------------------
	| Log the query into the app/storage/logs fodler
	|--------------------------------------------------------------------------
	|
	|
	*/
	'log' => true
);