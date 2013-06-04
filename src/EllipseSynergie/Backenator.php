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
	protected $client;
	
	/**
	 * The base url of the API
	 * 
	 * @var string
	 */
	public $baseUrl;
	
	/**
	 * Errors message bag use to set and get errors 
	 * from the request result
	 * 
	 * @var MessageBag
	 */
	protected $errors;
	
	/**
	 * The query string parameters
	 * 
	 * @var array
	 */
	protected $params = array();
	
	/**
	 * URI segments of the request
	 * 
	 * @var array
	 */
	protected $segments = array();
	
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
		$this->errors = new MessageBag;
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
			$this->client = $client;
			
		//Else, build a new one we a default Buzz\Browser object
		} else {
			
			//Create the Rest Client
			$this->client = new Client(new CurlClientInterface());
		}		
	}
	
	/**
	 * Find a model by its primary key.
	 *
	 * @param  mixed  $id
	 * @param  array  $columns
	 * @return Backenator
	 */
	public static function find($id, $columns = array())
	{	
		//Create the new instance
		$instance = $this->newInstance(array(), true);
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
		$this->params[$field] = $value;
	
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
		$this->segments[] = urlencode($segment);
	
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
		$url = $this->getBaseUrl() . $this->uri(); 
		
		//If we have parameters
		if(!empty($this->params)){
		
			//Add query string parameters to the URL
			$url .= '?' . http_build_query($this->params);
			
		}
		
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
		if(!empty($this->segments))
		{
			//for each segment to add
			foreach ($this->segments as $segment)
			{
				//Add the segment to the final URI
				$uri .= $segment . '/';
			}
		}
	
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
		return $this->client;		
	}
	
	/**
	 * Retrieve the first element
	 *
	 * @return Backenator|bool
	 */
	public function first()
	{		
		//Get elements
		$result = $this->get();
		
		//If we have multiple result
		if(is_array($result)){	

			//Get the first array element
			reset($result);
			return current($result);
		}
		
		return $result;
	}
	
	/**
	 * Request GET on backend
	 *
	 * @return Backenator|array
	 */
	public function get()
	{								
		//Build the url
		$url = $this->url();
		
		//Do the get resquest to the Backend
		$response = $this->client()->get($url);
		
		//Convert response to json
		$content = json_decode($response->getContent());
		
		//Log the request
		$this->log('GET', $url, $response->getContent());
		
		//Handle the get method
		$result = $this->handleGet($content, $response);
		
		//Set the request response
		$this->setResponse($response);
		
		return $result;		
	}
	
	/**
	 * Handle the get method
	 * 
	 * @param object $content
	 * @return Backenator|array|false
	 */
	protected function handleGet($content, \Buzz\Message\Response $response)
	{
		//Default
		$results = array();
		
		//If we have content
		if (!empty($content->results)) {
		
			//For each data result
			foreach ($content->results as $result) {
					
				//Create a new modal object
				$object = $this->newInstance(array(), true);
				
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
	
	/**
	 * Request POST on backend
	 * 
	 * @return bool
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
		
		//Handle the post method
		$result = $this->handlePost($content, $response);
		
		//Set the request response
		$this->setResponse($response);
		
		return $result;		
	}
	
	/**
	 * Handle the post method
	 *
	 * @param object $content
	 * @return bool
	 */
	protected function handlePost($content, \Buzz\Message\Response $response)
	{
		//If we have content
		if(!empty($content->{$this->primaryKey})) {
				
			//Force attribute set
			$this->setAttribute($this->primaryKey, $content->{$this->primaryKey});
			$this->setAttribute(self::CREATED_AT, $content->{self::CREATED_AT});
			
			$this->exists = true;
		
			return true;		
		}
		
		return false;
	}
	
	/**
	 * Request PUT on backend
	 *
	 * @return bool;
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
		
		//Handle the put method
		$result = $this->handlePut($content, $response);
		
		//Set the request response
		$this->setResponse($response);
		
		return $result;		
	}
	
	/**
	 * Handle the put method
	 *
	 * @param object $content
	 * @return bool
	 */
	protected function handlePut($content, \Buzz\Message\Response $response)
	{
		//If we have content
		if(!empty($content)){
					
			//Force attribute set
			$this->setAttribute(self::UPDATED_AT, $content->{self::UPDATED_AT});
			
			$this->exists = true;
					
			return true;
		}
		
		return false;
	}
	
	/**
	 * Request DELETE on backend
	 *
	 * @return bool;
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
		
		//Handle the delete method
		$result = $this->handleDelete($content, $response);
		
		//Set the request response
		$this->setResponse($response);
		
		return $result;
		
	}
	
	/**
	 * Handle the put method
	 *
	 * @param object $content
	 * @return bool
	 */
	protected function handleDelete($content, \Buzz\Message\Response $response)
	{	
		//If we have content
		if(!empty($content)){
					
			//Force attribute set
			$this->setAttribute(self::DELETED_AT, $content->{self::DELETED_AT});
			
			$this->exists = false;
		
			return true;
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
		return (count($this->errors) == 0 ? true : false);
	
	}
	
	/**
	 * Check if request has fail
	 *
	 * @return bool
	 */
	public function fail($errorName)
	{
		return $this->success()? false : true;	
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
		$this->response;		
	}
	
	/**
	 * Set the base url of each request
	 *
	 * @param string $url
	 */
	public function setBaseUrl($url)
	{
		$this->baseUrl = $url;
	}
	
	/**
	 * Return the base url
	 *
	 * @return string
	 */
	public function getBaseUrl()
	{
		$this->baseUrl;
	}
	
	/**
	 * Save the model to the database.
	 * 
	 * @param  array  $options
	 * @return bool
	 */
	public function save(array $options = array())
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
	 * Get errors
	 * 
	 * @return MessageBag
	 */
	public function errors()
	{
		return $this->errors;			
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

}