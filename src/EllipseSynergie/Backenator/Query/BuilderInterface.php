<?php 
namespace EllipseSynergie\Backenator\Query;

/**
 * Query builder class
 *
 */
interface BuilderInterface {
	/**
	 * Check if request has success
	 *
	 * @param object $content
	 * @param \Buzz\Message\Response $response
	 * @return boolean
	 */
	public function success($content, \Buzz\Message\Response $response);
	
	/**
	 * Build the request results
	 *
	 * @param object $content
	 * @return Backenator|array
	 */
	public function buildResults($content);
	
	/**
	 * GET method
	 *
	 * @return Backenator|array
	 */
	public function get();
	
	/**
	 * POST method.
	 *
	 * @param  array  $attributes
	 */
	public function post(array $attributes);
	
	/**
	 * PUT method
	 *
	 * @return bool;
	 */
	public function put(array $attributes);
	
	/**
	 * DELETE method
	 *
	 * @return bool;
	 */
	public function delete();
}