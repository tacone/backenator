<?php 
namespace EllipseSynergie\Backenator\Query;

/**
 * Query builder class
 *
 */
class BaseBuilder extends Builder {

	/**
	 * Check if request has success
	 *
	 * @param object $content
	 * @param \Buzz\Message\Response $response
	 * @return boolean
	 */
	public function success($content, \Buzz\Message\Response $response)
	{
		//If the request is a succes
		if(!empty($content->success)){
			return true;
		}
	
		return false;
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
				$this->setPerPage($content->count);
			}
	
			//If we only have one result
			if(count($results) === 1){
				return $results[0];
			}
	
			//Return the results
			return $results;
		}
			
		return $results;
	}
}