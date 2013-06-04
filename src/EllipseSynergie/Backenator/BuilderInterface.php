<?php 
namespace EllipseSynergie\Backenator;

use EllipseSynergie\Backenator;


interface BuilderInterface {
	
	/**
	 * Check if the query is a success
	 * 
	 * @param object $content
	 * @param \Buzz\Message\Response $response
	 * @return bool
	 */
	public function success($content, \Buzz\Message\Response $response);
	
	/**
	 * Set a model instance for the model being queried.
	 *
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function setModel(Backenator $model);
	
	/**
	 * Build results with the provided content
	 *
	 * @param object $content
	 * @return Backenator|array|false
	 */
	public function buildResults($content);
	
}