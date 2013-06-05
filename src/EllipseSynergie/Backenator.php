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
	 * Retrieve elements
	 * 
	 * @return Backenator|array
	 */
	public function get()
	{
		$query = $this->newQuery();
		$result = $query->get();
		$this->setResponse($query->getResponse());
	
		return $result;
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
	 * Find a model by its primary key.
	 *
	 * @param  mixed  $id
	 * @param  array  $columns
	 * @return Backenator
	 */
	public static function find($id, $columns = array())
	{	
		//Create the new instance
		$instance = static::newInstance(array(), true);
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
	 * Get the client object
	 * 
	 * @return \Buzz\Browser
	 */
	public function getClient()
	{
		return $this->client;		
	}
	
	/**
	 * Get a new query builder for the model's table.
	 *
	 * @param  bool  $excludeDeleted
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function newQuery($excludeDeleted = true)
	{		
		//Create query build
		$queryBuilder = new Backenator\Query\BaseBuilder($this);
		$builder = new Backenator\Builder($queryBuilder);
		
		// Once we have the query builders, we will set the model instances so the
		// builder can easily access any information it may need from the model
		// while it is constructing and executing various queries against it.
		$builder->setModel($this);
	
		return $builder;
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
		return $this->baseUrl;
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
	
	/**
	 * Get segment
	 *
	 * @return array
	 */
	public function getSegments()
	{
		return $this->segments;
	}
	
	/**
	 * Get parameters
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
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
	 * Get errors
	 *
	 * @return MessageBag
	 */
	public function errors()
	{
		return $this->errors;
	}
}