<?php 
namespace EllipseSynergie\Backenator;

use EllipseSynergie\Backenator;
use EllipseSynergie\Backenator\Query\Builder as QueryBuilder;

/**
 * Builder use by Backenator
 */
class Builder {
	
	/**
	 * Create a new Eloquent query builder instance.
	 *
	 * @param  \Illuminate\Database\Query\Builder  $query
	 * @return void
	 */
	public function __construct(QueryBuilder $query)
	{
		$this->query = $query;
	}
	
/**
	 * Set a model instance for the model being queried.
	 *
	 * @param  \EllipseSynergie\Backenator  $model
	 * @return \EllipseSynergie\Backenator\Builder
	 */
	public function setModel(Backenator $model)
	{
		$this->model = $model;

		$this->query->from($model->getTable());

		return $this;
	}
	
	/**
	 * Get request
	 */
	public function get()
	{
		$result = $this->query->get();
		$this->setResponse($this->query->getResponse());
		
		return $result;
	}
	
	/**
	 * Insert a reccord
	 * 
	 * @param array $values
	 * @return int
	 */
	public function insert(array $values)
	{
		$result = $this->query->post($values);
		$this->setResponse($this->query->getResponse());
		
		return $result;
	}
	
	/**
	 * Insert a new record and get the value of the primary key.
	 *
	 * @param  array   $values
	 * @param  string  $sequence
	 * @return int
	 */
	public function insertGetId(array $values, $sequence = null)
	{
		$result = $this->query->post($values);
		$this->setResponse($this->query->getResponse());
		
		return $result;
	}
	
	/**
	 * Update a record in the database.
	 *
	 * @param  array  $values
	 * @return int
	 */
	public function update(array $values)
	{
		$result = $this->query->put($values);
		$this->setResponse($this->query->getResponse());
		
		return $result;
	}
	
	/**
	 * Delete a record from the database.
	 *
	 * @return int
	 */
	public function delete()
	{
		$result = $this->query->delete();
		$this->setResponse($this->query->getResponse());
		
		return $result;
	}

	/**
	 * Set the response
	 *
	 * @param \Buzz\Message\Response $response
	 */
	public function setResponse(\Buzz\Message\Response $response)
	{
		$this->response = $response;
	}
	
	/**
	 * Return the response object
	 *
	 * @return \Buzz\Message\Response|null
	 */
	public function getResponse()
	{
		return $this->response;
	}
}