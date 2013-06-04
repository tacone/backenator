<?php 
namespace EllipseSynergie\Backenator;

use EllipseSynergie\Backenator;


interface BuilderInterface {
		
	/**
	 * Handle the get method
	 *
	 * @param object $content
	 * @return bool
	 */
	public function get($content, \Buzz\Message\Response $response);
	
	/**
	 * Handle the put method
	 *
	 * @param object $content
	 * @return bool
	 */
	public function put($content, \Buzz\Message\Response $response);
	
	/**
	 * Handle the post method
	 *
	 * @param object $content
	 * @return bool
	 */
	public function post($content, \Buzz\Message\Response $response);
	
	/**
	 * Handle the delete method
	 *
	 * @param object $content
	 * @return bool
	 */
	public function delete($content, \Buzz\Message\Response $response);
	
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