<?php 
namespace EllipseSynergie\Backenator\Query;

/**
 * Base query builder
 * 
 * @author Maxime Beaudoin <maxime.beaudoin@ellipse-synergie.com>
 */
class BaseBuilder extends Builder {

	/**
	 * Check if request has success
	 *
	 * @param object $content
	 * @param \Buzz\Message\Response $response
	 * @return boolean
	 */
	public function success(\Buzz\Message\Response $response)
	{		
		$content = json_decode($response->getContent());
		
		//Check the status code from the request
		if(!$response->isSuccessful())
		{					
			//If we have errors
			if (!empty($content->errors)) {
				$this->errors = $content->errors;
			}
			
			return false;		
		}
	
		return true;
	}
	
	/**
	 * Build the request results
	 *
	 * @param object $content
	 * @return Backenator|array
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
				$this->model->setPerPage($content->count);
			}
	
			//Return the results
			return $results;
		}
			
		return $results;
	}
}