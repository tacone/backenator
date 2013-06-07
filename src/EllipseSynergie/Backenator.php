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
	 * Errors
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
	 */
	public function __construct(array $attributes = array())
	{
		//Parent constructor
		parent::__construct($attributes);
		
		//Set the default base url from configuration
		$this->setBaseUrl(\Config::get('backenator::baseUrl'));
		
		//Factory the client use to do request
		$this->client = new Client(new CurlClientInterface());	
	}

	/**
	 * Retrieve elements
	 * 
	 * @return Backenator|array
	 */
	public function get()
	{
		//Create the query builder object
		$query = $this->newQuery();
		
		//Get entries
		$result = $query->get();
		
		//Set the response object from the query to the current model
		$this->setResponse($query->getResponse());
		
		//Set errorst from the query to the current model
		$this->errors = $query->errors();
	
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
	 * Find a model by primary key.
	 *
	 * @param  mixed  $id
	 * @param  array  $columns
	 * @return Backenator
	 */
	public static function find($id, $columns = array())
	{	
		//Create the new instance
		$instance = new static(array(), true);
		$instance->{$instance->primaryKey} = $id;
		
		$instance->addEntryKey();
	
		return $instance->first();	
	}
	
	/**
	 * Destroy the models for the given id.
	 *
	 * @param  array|int  $ids
	 * @return void
	 */
	public static function destroy($id)
	{
		//Create the new instance
		$instance = new static(array(), true);
		$instance->{$instance->primaryKey} = $id;
		$instance->delete();
	
		return $instance;
	}
	
	/**
	 * Update the model in the database.
	 *
	 * @param  array  $attributes
	 * @return mixed
	 */
	public function update(array $attributes = array())
	{
		return $this->performUpdate($this->newQuery());
	}
	
	/**
	 * Insert the model in the database.
	 *
	 * @param  array  $attributes
	 * @return mixed
	 */
	public function insert()
	{
		return $this->performInsert($this->newQuery());
	}
	
	/**
	 * Delete the model from the database.
	 *
	 * @return bool|null
	 */
	public function delete()
	{
		if ($this->fireModelEvent('deleting') === false) return false;
	
		// Here, we'll touch the owning models, verifying these timestamps get updated
		// for the models. This will allow any caching to get broken on the parents
		// by the timestamp. Then we will go ahead and delete the model instance.
		$this->touchOwners();
	
		$this->performDeleteOnModel();
	
		$this->exists = false;
	
		// Once the model has been deleted, we will fire off the deleted event so that
		// the developers may hook into post-delete operations. We will then return
		// a boolean true as the delete is presumably successful on the database.
		$this->fireModelEvent('deleted', false);
	
		return true;
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
	public function segment($segment, $first = false)
	{
		//Encode the segment
		$segment = urlencode($segment);
		
		//If we want to add the segment at the begin
		if($first == true){
			array_unshift($this->segments, $segment);
			
		//Else we want to add the segment at the end
		} else {		
			array_push($this->segments, $segment);		
		}		
	
		return $this;	
	}
	
	/**
	 * Set the client
	 * 
	 * @return \Buzz\Browser
	 */
	public function setClient(\Buzz\Browser $client)
	{
		$this->client = $client;		
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
	 * Get the modal primary key
	 * 
	 * @return string
	 */
	public function getPrimaryKey()
	{
		return $this->primaryKey;		
	}
	
	/**
	 * Create a new query builder for the model's table.
	 *
	 * @param  bool  $excludeDeleted // not supported yet
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function newQuery($excludeDeleted = true)
	{		
		
		//Builder classnam
		$builderName = \Config::get('backenator::queryBuilder');
		
		//Create query build
		$queryBuilder = new $builderName($this);
		$builder = new Backenator\Builder($queryBuilder);
		
		// Once we have the query builders, we will set the model instances so the
		// builder can easily access any information it may need from the model
		// while it is constructing and executing various queries against it.
		$builder->setModel($this);
	
		return $builder;
	}

	/**
	 * Perform a model update operation.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder
	 * @return bool
	 */
	protected function performUpdate($query)
	{
		$dirty = $this->getDirty();
	
		if (count($dirty) > 0)
		{
			// If the updating event returns false, we will cancel the update operation so
			// developers can hook Validation systems into their models and cancel this
			// operation if the model does not pass validation. Otherwise, we update.
			if ($this->fireModelEvent('updating') === false)
			{
				return false;
			}
	
			// First we need to create a fresh query instance and touch the creation and
			// update timestamp on the model which are maintained by us for developer
			// convenience. Then we will just continue saving the model instances.
			if ($this->timestamps)
			{
				$this->updateTimestamps();
	
				$dirty = $this->getDirty();
			}
	
				
			//Add the id of the entry in the request
			$this->addEntryKey();
				
			// Do the query
			$query->update($dirty);
			$this->setResponse($query->getResponse());
			$this->errors = $query->errors();
	
			// Once we have run the update operation, we will fire the "updated" event for
			// this model instance. This will allow developers to hook into these after
			// models are updated, giving them a chance to do any special processing.
			$this->fireModelEvent('updated', false);
		}
	
		return true;
	}
	
	/**
	 * Perform a model insert operation.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder
	 * @return bool
	 */
	protected function performInsert($query)
	{
		if ($this->fireModelEvent('creating') === false) return false;
	
		// First we'll need to create a fresh query instance and touch the creation and
		// update timestamps on this model, which are maintained by us for developer
		// convenience. After, we will just continue saving these model instances.
		if ($this->timestamps)
		{
			$this->updateTimestamps();
		}
	
		// Do the query
		$id = $query->insert($this->attributes);
		$this->setResponse($query->getResponse());
		$this->errors = $query->errors();
		
		// Set id
		$this->setAttribute($this->primaryKey, $id);
	
		// We will go ahead and set the exists property to true, so that it is set when
		// the created event is fired, just in case the developer tries to update it
		// during the event. This will allow them to do so and run an update here.
		$this->exists = true;
	
		$this->fireModelEvent('created', false);
	
		return true;
	}
	
	/**
	 * Perform the actual delete query on this model instance.
	 *
	 * @return void
	 */
	protected function performDeleteOnModel()
	{
		$query = $this->newQuery();
	
		//Add the id of the entry in the request
		$this->addEntryKey();
	
		if ($this->softDelete)
		{
			$query->update(array(static::DELETED_AT => new DateTime));
		}
		else
		{
			$query->delete();
		}
	
		$this->setResponse($query->getResponse());
		$this->errors = $query->errors();
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
		return $this->fail()? false : true;	
	}
	
	/**
	 * Check if request has fail
	 *
	 * @return bool
	 */
	public function fail()
	{
		return !empty($this->errors);	
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
	 * Add the entry id in the request
	 */
	public function addEntryKey()
	{
		//If we want to add automaticly the id to the request
		if(\Config::get('backenator::autoId') == true){
		
			//Get the current id
			$id = $this->{$this->primaryKey};
		
			if($id){
				$this->segment($id, true);
			}
		}
	}
	
	/**
	 * Helper to return rapidly the current request URL
	 * 
	 * @return Buzz\Message\Request|null
	 */
	public function getRequestUrl()
	{		
		//If we have a last resquest
		if($this->getClient()->getLastRequest()){		
			return $this->getClient()->getLastRequest()->getUrl();
		}
	}
}