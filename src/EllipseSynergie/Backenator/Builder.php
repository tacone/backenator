<?php 
namespace EllipseSynergie\Backenator;

use EllipseSynergie\Backenator;

class Builder implements BuilderInterface {
	
	/**
	 * (non-PHPdoc)
	 * @see \EllipseSynergie\Backenator\BuilderInterface::get()
	 */
	public function get($content, \Buzz\Message\Response $response)
	{
		return $this->isSuccess($content);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \EllipseSynergie\Backenator\BuilderInterface::post()
	 */
	public function post($content, \Buzz\Message\Response $response)
	{	
		return $this->isSuccess($content);
	}

	/**
	 * (non-PHPdoc)
	 * @see \EllipseSynergie\Backenator\BuilderInterface::put()
	 */
	public function put($content, \Buzz\Message\Response $response)
	{
		return $this->isSuccess($content);
	}

	/**
	 * (non-PHPdoc)
	 * @see \EllipseSynergie\Backenator\BuilderInterface::delete()
	 */
	public function delete($content, \Buzz\Message\Response $response)
	{
		return $this->isSuccess($content);
	}
	
	/**
	 * Check if the query is a success
	 * 
	 * @param object $content
	 * @return bool
	 */
	protected function isSuccess($content)
	{
		//If the request is a succes
		if(!empty($content->success)){
			return true;
		}
		
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \EllipseSynergie\Backenator\BuilderInterface::setModel()
	 */
	public function setModel(Backenator $model)
	{
		$this->model = $model;
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \EllipseSynergie\Backenator\BuilderInterface::buildResults()
	 */
	public function buildResults($content)
	{
		//Default
		$results = array();
	
		//If we have content
		if (!empty($content->results)) {
	
			//For each data result
			foreach ($content->results as $result) {
					
				//Create a new modal object
				$object = $this->model->newInstance(array(), true);
	
				//For each result data
				foreach ($result as $datak => $datav) {
	
					//Force attribute set
					$object->setAttribute($datak, $datav);
				}
					
				//Push the object in the results
				array_push($results, $object);
			}
	
			// Set results count
			if (!empty($content->count)) {
				$this->setPerPage($content->count);
			}
	
			//If we only have one result
			if(count($results) === 1){
				return $results[0];
			}
	
			//Return the results
			return $results;
		}
			
		return false;
	}
}