<?php 
namespace EllipseSynergie\Backenator\Query;

/**
 * Query builder interface
 * 
 * @author Maxime Beaudoin <maxime.beaudoin@ellipse-synergie.com>
 */
interface BuilderInterface {
	/**
	 * Check if request has success
	 *
	 * @param \Buzz\Message\Response $response
	 * @return boolean
	 */
	public function success(\Buzz\Message\Response $response);
	
	/**
	 * Get the insert id
	 *
	 * @param \Buzz\Message\Response $response
	 * @return boolean
	 */
	public function insertId(\Buzz\Message\Response $response);
	
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