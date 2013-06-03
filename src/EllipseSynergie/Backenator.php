<?php 
namespace EllipseSynergie;

use \Buzz\Browser as Client;
use \Buzz\Client\Curl as CurlClientInterface;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\MessageBag;

/**
 * ORM (Object Relational Mapper) build on top of Eloquent that maps REST resources
 * 
 * @author Maxime Beaudoin <maxime.beaudoin@ellipse-synergie.com>
 */
abstract class Backenator extends Eloquent {
	
	/**
	 * The client use to do the request
	 * 
	 * @var Buzz\Browser
	 */
	protected $_client;
	
	/**
	 * The base url of the API
	 * 
	 * @var string
	 */
	public $base_url;
	
	/**
	 * Errors message bag use to set and get errors 
	 * from the request result
	 * 
	 * @var MessageBag
	 */
	protected $_errors;
	
	/**
	 * The query string parameters
	 * 
	 * @var array
	 */
	protected $_params = array();
	
	/**
	 * URI segments of the request
	 * 
	 * @var array
	 */
	protected $_segments = array();
	
	/**
	 * Constructor
	 * 
	 * @param array $attributes
	 * @param Buzz\Browser $client
	 */
	public function __construct(array $attributes = array(), \Buzz\Browser $client = null)
	{
		//Fill attributes
		$this->fill($attributes, true);
		
		//Factory the client
		$this->clientFactory($client);		

		//Create errors message bag
		$this->_errors = new MessageBag;
	}
	
	/**
	 * Create the new client use for the request
	 * 
	 * @param Buzz\Browser $client
	 */
	public function clientFactory(\Buzz\Browser $client = null)
	{
		//If we have a custom client
		if (!empty($client)) {
			
			//Set the client
			$this->_client = $client;
			
		//Else, build a new one we a default Buzz\Browser object
		} else {
			
			//Create the Rest Client
			$this->_client = new Client(new CurlClientInterface());
		}		
	}
	
	/**
	 * Find a model by its primary key.
	 *
	 * @param  mixed  $id
	 * @return Backenator
	 */
	public static function find($id)
	{	
		//Create the new instance
		$instance = new static;
		$instance->segment($id);
	
		return $instance->first();	
	}
	
	/**
	 * Add query string parameter to the request
	 *
	 * @param string $field
	 * @param string $uri
	 * @return Backenator
	 */
	public function where($field, $value)
	{	
		//Add param to the current object
		$this->_params[$field] = $value;
	
		return $this;	
	}
	
	/**
	 * Add segment to the request URI
	 *
	 * @param string $segment
	 * @return Backenator
	 */
	public function segment($segment)
	{
		//Encode and push a new segment into the array
		$this->_segments[] = urlencode($segment);
	
		return $this;	
	}
	
	/**
	 * Build the request URL
	 * 
	 * @return string
	 */
	public function url()
	{		
		//Build the url
		$url = $this->base_url . $this->uri(); 
		
		//Add query string parameters to the URL
		$url .= '?' . http_build_query($this->_params);
		
		return $url;		
	}
	
	/**
	 * Build the uri string
	 *
	 * @return string
	 */
	public function uri()
	{
		//Create the base URI
		$uri = $this->table . '/';
	
		//If we have segment to add
		if(!empty($this->_segments))
		{
			//for each segment to add
			foreach ($this->_segments as $segment)
			{
				//Add the segment to the final URI
				$uri .= $segment . '/';
			}
		}
	
		//Unset the uri segments for the next resquest
		$this->_segments = array();
	
		//Return the uri and remove the latest trailing slash
		return substr($uri, 0, -1);
	}
	
	/**
	 * Get the client object
	 * 
	 * @return \Buzz\Browser
	 */
	public function client()
	{
		return $this->_client;		
	}
	
	/**
	 * Retrieve the first element
	 *
	 * @return Backenator|boolean
	 */
	public function first()
	{		
		//Default
		$first = array();
	
		//Build the url
		$url = $this->buildRequestUrl();
	
		//Do the get resquest to the Backend
		$response = $this->client()->get($url);
	
		//Convert response to json
		$content = json_decode($response->getContent());
	
		//Log the request
		$this->log('GET', $url, $response->getContent());
	
		//If we have content
		if(!empty($content)){
	
			//If the API request succeed
			if ($content->success == true) {
	
				//If we have results
				if(!empty($content->results))
				{
					//Get the first element
					$first = array_shift($content->results);
						
				}
	
				//For each result
				foreach ($first as $datak => $datav) {
	
					//Set the data to the model object
					$this->setAttribute($datak, $datav);
				}
					
			//Else the API request fail
			} else {				
				$this->clientErrors($response);
			}
		}
			
		return $this;
	
	}
	
	/**
	 * Request GET on backend
	 *
	 * @return mixed
	 */
	public function get()
	{						
		//Default
		$results = array();
		
		//Build the url
		$url = $this->url();
		
		//Do the get resquest to the Backend
		$response = $this->client()->get($url);
		
		//Convert response to json
		$content = json_decode($response->getContent());
		
		//Log the request
		$this->log('GET', $url, $response->getContent());
		
		//If we have content
		if (!empty($content)) {
		
			//If the API request succeed
			if ($content->success == true) {
						
				//For each data result 
				foreach ($content->results as $result) {
					
					//Create a new modal object
					$object = new static;
			
					//For each result
					foreach ($result as $datak => $datav) {
						
						//Set the data to the model object
						$object->setAttribute($datak, $datav);
					}
			
					//Push the object in the results
					array_push($results, $object);
				}

				// Set results count
				if (!empty($content->count)) {
					$this->setPerPage($content->count);
				}
			
				//Return the results
				return $results;
			
			//Else we have a errors
			} else {		
				$this->clientErrors($response);
			}			
		}
			
		return false;
		
	}
	
	/**
	 * Request POST on backend
	 * 
	 * @return mixed;
	 */
	public function post()
	{				
		//Build the url
		$url = $this->url();
		
		//Build data query
		$data = http_build_query($this->attributes);
		
		//Do the get resquest to the Backend
		$response = $this->client()->post($url, array(), $data);
		
		//Convert response to json
		$content = json_decode($response->getContent());
		
		//Log the request
		$this->log('POST', $url, $response->getContent());
		
		//If we have content
		if(!empty($content)) {
		
			//If the request success
			if ($content->success == true) {
				
				//Set attribute
				$this->setAttribute('id', $content->id);
				$this->setAttribute('created_at', $content->created_at);
			
				return $content;
			
			//Else we have a errors
			} else {		
				$this->clientErrors($response);
			}			
		}
		
		return false;
		
	}
	
	/**
	 * Request PUT on backend
	 *
	 * @todo replace the putfile when the backend will be updated
	 * @return mixed;
	 */
	public function put()
	{		
		//Build the url
		$url = $this->url();
		
		//Build data query
		$data = http_build_query($this->attributes);
		
		//Do the get resquest to the Backend
		$response = $this->client()->put($url, array(), $data);
		
		//Convert response to json
		$content = json_decode($response->getContent());
		
		//Log the request
		$this->log('PUT', $url, $response->getContent());
		
		//If we have content
		if(!empty($content)){
		
			//If the request success
			if ($content->success == true) {
			
				//Set attribute
				$this->setAttribute('updated_at', $content->updated_at);
			
				return $content;
			
			//Else we have a errors
			} else {		
				$this->clientErrors($response);
			}			
		}
		
		return false;
		
	}
	
	/**
	 * Request DELETE on backend
	 *
	 * @return mixed;
	 */
	public function delete()
	{				
		//Add the find id
		$this->segment($this->id);
		
		//Build the url
		$url = $this->url();
		
		//Do the get resquest to the Backend
		$response = $this->client()->delete($url, array());

		//Convert response to json
		$content = json_decode($response->getContent());
		
		//Log the request
		$this->log('DELETE', $url, $response->getContent());
		
		//If we have content
		if(!empty($content)){
		
			//If the request success
			if ($content->success == true) {
					
				//Set attribute
				$this->setAttribute('deleted_at', $content->deleted_at);
			
				return $content;
			
			//Else we have a errors
			} else {		
				$this->clientErrors($response);
			}
		}
		
		return false;
		
	}
	
	/**
	 * Log the request
	 * 
	 * @param string $method
	 * @param string $url
	 * @param mixed $result
	 */
	public function log($method, $url, $result)
	{
	}
	
	/**
	 * Check if request has succeed
	 *
	 * @return bool
	 */
	public function success()
	{
		return (count($this->_errors) == 0 ? true : false);
	
	}
	
	/**
	 * Check if error $errorName is present
	 *
	 * @param string $errorName
	 * @return bool
	 */
	public function fail($errorName)
	{
		if (!empty($this->_errors[$errorName])) {
			return true;
		}
	
		return false;
	
	}	
	
	/**
	 * Fire errors events and other things
	 * 
	 * @param unknown $response
	 */
	public function clientErrors($response)
	{
		//Get the query result
		$result = json_decode($response->getContent());
			
		//Debug
		#$this->_debug['status_code'] = $response->getStatusCode();
		#$this->_debug['headers'] = $response->getHeaders();
		#$this->_debug['content'] = $response->getContent();
		
		//If we have a 401 error
		if ($response->getStatusCode() == 401){
			
			//Fire invalid token event
			#Event::fire('invalid_token');		
		} 
		
		//Iif we have errors
		if (!empty($result->errors)) {				
			$this->_errors = $result->errors;			
		}

	} // clientErrors()
	
	/**
	 * Save the model to the database.
	 * 
	 * @return bool
	 */
	public function save()
	{				
		//If the primary key attribute is set
		if ($this->getAttribute($this->primaryKey)) {			
			
			//Update the element
			return $this->put();	

		//Else create a new model
		} else {
			
			//Create a new element
			return $this->post();
		}
		
	}
	
	/**
	 * Get the format for database stored dates.
	 *
	 * @return string
	 */
	protected function getDateFormat()
	{
		return 'Y-m-d H:i:s';
	}
	
	/**
	 * Get errors
	 */
	public function errors()
	{
		return $this->_errors;
			
	} // getErrors()

} // Backenator