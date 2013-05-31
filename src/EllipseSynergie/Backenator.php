<?php 
namespace EllipseSynergie;

use \Buzz\Browser as Client;
use \Buzz\Client\Curl as CurlClientInterface;

/**
 * Model base class
 * 
 * @author Ellipse Synergie <support@ellipse-synergie.com>
 */
abstract class Backenator {

	
	/**
	 * Containt the currect object data
	 * 
	 * @var array
	 */
	protected $_attributes = array();

	/**
	 * The model attribute's original state.
	 *
	 * @var array
	 */
	protected $_original = array();
	
	/**
	 * The specific id to find
	 */
	protected $_find_id;
	
	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primary_key = 'id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $_fillable = array();
	
	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $_guarded  = array();
	
	/**
	 * The current model URI
	 */
	protected static $_model_uri;
	
	/**
	 * Containt the rest client
	 * 
	 * @var Buzz\Browser
	 */
	protected $_client;
	
	/**
	 * The base url
	 * 
	 * @var string
	 */
	protected $_base_url;
	
	/**
	 * Debug data
	 * 
	 * @var mixed
	 */
	protected $_debug = array();
	
	/**
	 * Containt errors
	 * 
	 * @var mixed
	 */
	protected $_errors = array();
	
	/**
	 * The query string params
	 * 
	 * @var array
	 */
	protected $_params = array();
	
	/**
	 * URI segments to add to the query
	 * 
	 * @var array
	 */
	protected $_uri_segments = array();
	
	/**
	 * Indicates if all mass assignment is enabled.
	 *
	 * @var bool
	 */
	protected static $unguarded = false;

	/**
	 * Results' count (in case of pagination)
	 *
	 * @var int
	 */
	protected $_count = 0;

	/**
	 * The array of booted models.
	 *
	 * @var array
	 */
	protected static $booted = array();
	
	/**
	 * Constructor
	 */
	public function __construct(array $attributes = array(), $client = null)
	{
		//Fill attributes
		$this->fill($attributes, true);
		
		//Factory the client
		$this->clientFactory($client);	
		
		//Boot model
		if ( ! isset(static::$booted[get_class($this)]))
		{
			static::_boot();

			static::$booted[get_class($this)] = true;
		}
			
	} // __construct()
	
	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	protected static function _boot()
	{		
	} // _boot()
	
	/**
	 * Create a new client
	 */
	public function clientFactory(\Buzz\Browser $client = null)
	{
		//If we have a custom client
		if (!empty($client)) {
			
			//Set the client
			$this->_client = $client;
			
		//Else, build a new one
		} else {
			
			//Create the Rest Client
			$this->_client = new Client(new CurlClientInterface());
		}
		
	} // clientFactory()
	
	/**
	 * Execute a query for a single record by ID.
	 *
	 * @param  int    $id
	 * @return mixed
	 */
	public function findQuery($id)
	{
		$this->_find_id = $id;
		
		return $this->first();
		
	} // findQuery()
	
	/**
	 * Execute a query for a single record by ID.
	 *
	 * @param  int    $id
	 * @return mixed
	 */
	public static function find($id)
	{	
		$instance = new static;		
	
		return $instance->findQuery($id);
	
	} // find()
	
	/**
	 * Add a basic where clause to the query.
	 *
	 * @param string $field
	 * @param string $uri
	 * @return this
	 */
	public function where($field, $value)
	{	
		//Add param to the current object
		$this->_params[$field] = $value;
	
		//Return the full object
		return $this;
	
	} // where()
	
	
	/**
	 * Build the uri string
	 * 
	 * @return string
	 */
	public function buildUriString()
	{
		$uri = $this->getModelUri() . '/';
		
		//If we have a specific id to find
		if(!empty($this->_find_id)){
			$uri .= $this->_find_id . '/';
		}
		
		if(!empty($this->_uri_segments))
		{
			foreach ($this->_uri_segments as $segment)
			{
				$uri .= $segment . '/';
			}
		}
		
		//Flush the uri segments for the next resquest
		$this->_uri_segments = array();
		
		//Return the uri and remove the last trailing slash
		return substr($uri, 0, -1);
		
	} // buildUriString()
	
	/**
	 * Add something to uri
	 *
	 * @param string $segment
	 * @return this
	 */
	public function addUriSegment($segment)
	{
		$this->_uri_segments[] = urlencode($segment);
	
		return $this;
	
	} // addUriSegment()
	
	/**
	 * Build the request URL
	 * 
	 * @return string
	 */
	public function buildRequestUrl()
	{		
		//Build the url
		$url = $this->_base_url . $this->buildUriString(); 
		
		/*
		// User's token
		if (Session::get('token') != false) {
			$this->_params['token'] = Session::get('token');
		}
		// Class's token
		if (Session::get('ctoken') != false) {
			$this->_params['ctoken'] = Session::get('ctoken');
		}*/
		
		//Add the custom params to the query
		$url .= '?' . http_build_query($this->_params);
		
		//Return the full url with params
		return $url;
		
	} // buildRequestUrl()
	
	/**
	 * Get the client object
	 * 
	 * @return \Buzz\Browser
	 */
	public function getClient()
	{
		return $this->_client;
		
	}  // getClient()
	
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
		$response = $this->getClient()->get($url);
	
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
	
	} // first()
	
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
		$url = $this->buildRequestUrl();
		
		//Do the get resquest to the Backend
		$response = $this->getClient()->get($url);
		
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
					$this->_setCount($content->count);
				}
			
				//Return the results
				return $results;
			
			//Else we have a errors
			} else {		
				$this->clientErrors($response);
			}
			
		///Else we have no content return by the request
		} else {
			$this->_debug['no-content'] = true;
		}
			
		return false;
		
	} // get()
	
	/**
	 * Request POST on backend
	 * 
	 * @return mixed;
	 */
	public function post()
	{				
		//Build the url
		$url = $this->buildRequestUrl();
		
		//Build data query
		$data = http_build_query($this->_attributes);
		
		//Do the get resquest to the Backend
		$response = $this->getClient()->post($url, array(), $data);
		
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
			
		///Else we have no content return by the request
		} else {
			$this->_debug['no-content'] = true;
		}
		
		return false;
		
	} // post()
	
	/**
	 * Request PUT on backend
	 *
	 * @todo replace the putfile when the backend will be updated
	 * @return mixed;
	 */
	public function put()
	{		
		//Build the url
		$url = $this->buildRequestUrl();
		
		//Build data query
		$data = http_build_query($this->_attributes);
		
		//Do the get resquest to the Backend
		$response = $this->getClient()->put($url, array(), $data);
		
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
			
		///Else we have no content return by the request
		} else {
			$this->_debug['no-content'] = true;
		}
		
		return false;
		
	} // put()
	
	/**
	 * Request DELETE on backend
	 *
	 * @return mixed;
	 */
	public function delete()
	{				
		//Add the find id
		$this->_find_id = $this->id;
		
		//Build the url
		$url = $this->buildRequestUrl();
		
		//Do the get resquest to the Backend
		$response = $this->getClient()->delete($url, array());

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
			
		///Else we have no content return by the request
		} else {
			$this->_debug['no-content'] = true;
		}
		
		return false;
		
	} // delete()
	
	/**
	 * Fill the model with an array of attributes.
	 *
	 * @param  array  $attributes
	 * @param bool $force Force mass asignment
	 * @return Backenator
	 */
	public function fill(array $attributes, $force = false)
	{
		foreach ($attributes as $key => $value)
		{
			// The developers may choose to place some attributes in the "fillable"
			// array, which means only those attributes may be set through mass
			// assignment to the model, and all others will just be ignored.
			if ($this->isFillable($key) OR $force == true)
			{
				$this->setAttribute($key, $value);
			}
			elseif ($this->totallyGuarded())
			{
				throw new MassAssignmentException($key);
			}
		}
	
		return $this;
		
	} // fill()
	
	/**
	 * Determine if the given attribute may be mass assigned.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function isFillable($key)
	{
		if (static::$unguarded) return true;
	
		// If the key is in the "fillable" array, we can of course assume tha it is
		// a fillable attribute. Otherwise, we will check the guarded array when
		// we need to determine if the attribute is black-listed on the model.
		if (in_array($key, $this->_fillable)) return true;
	
		if ($this->isGuarded($key)) return false;
	
		return empty($this->_fillable) and ! starts_with($key, '_');
	}
	
	/**
	 * Determine if the given key is guarded.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function isGuarded($key)
	{
		return in_array($key, $this->_guarded) or $this->_guarded == array('*');

	} // isGuarded()
	
	/**
	 * Determine if the model is totally guarded.
	 *
	 * @return bool
	 */
	public function totallyGuarded()
	{
		return count($this->fillable) == 0 and $this->_guarded == array('*');

	} // totallyGuarded()
	
	/**
	 * Set an attribute
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function setAttribute($key, $value)
	{
		// First we will check for the presence of a mutator for the set operation
		// which simply lets the developers tweak the attribute as it is set on
		// the model, such as "json_encoding" an listing of data for storage.
		if ($this->hasSetMutator($key))
		{
			$method = 'set'.studly_case($key).'Attribute';
		
			return $this->{$method}($value);
		}
		
		$this->_attributes[$key] = $value;
	
	} // setAttribute()
	
	/**
	 * Get an attribute from the model.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function getAttribute($key)
	{
		$inAttributes = array_key_exists($key, $this->_attributes);
		
		// If the key references an attribute, we can just go ahead and return the
		// plain attribute value from the model. This allows every attribute to
		// be dynamically accessed through the _get method without accessors.
		if ($inAttributes or $this->hasGetMutator($key))
		{
			return $this->_getAttributeValue($key);
		}
	
		// If the key references an attribute, we can just go ahead and return the
		// plain attribute value from the model. This allows every attribute to
		// be dynamically accessed through the _get method without accessors.
		if ($inAttributes){
			return $this->_attributes[$key];
		}
		
	} // getAttribute()
	
	/**
	 * Get a plain attribute (not a relationship).
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	protected function _getAttributeValue($key)
	{
		$value = $this->getAttributeFromArray($key);
	
		// If the attribute has a get mutator, we will call that then return what
		// it returns as the value, which is useful for transforming values on
		// retrieval from the model to a form that is more useful for usage.
		if ($this->hasGetMutator($key))
		{
			return $this->_mutateAttribute($key, $value);
		}
	
		return $value;
		
	} // _getAttributeValue()
	
	/**
	 * Get an attribute from the $attributes array.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	protected function getAttributeFromArray($key)
	{
		if (array_key_exists($key, $this->_attributes))
		{
			return $this->_attributes[$key];
		}
	} // getAttributeFromArray()
	
	/**
	 * Convert the model instance to JSON.
	 *
	 * @param  int  $options
	 * @return string
	 */
	public function toJson($options = 0)
	{
		return json_encode($this->toArray(), $options);
		
	} // toJson()
	
	/**
	 * Return attributes as array
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->_attributes;

	} // toArray()
	
	/**
	 * Log the request
	 * 
	 * @param string $method
	 * @param string $url
	 * @param mixed $result
	 */
	public function log($method, $url, $result)
	{
	} // log()
	
	/**
	 * Determine if a get mutator exists for an attribute.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function hasGetMutator($key)
	{
		return method_exists($this, 'get'.studly_case($key).'Attribute');
		
	} // hasGetMutator()
	
	/**
	 * Get the value of an attribute using its mutator.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return mixed
	 */
	protected function _mutateAttribute($key, $value)
	{
		return $this->{'get'.studly_case($key).'Attribute'}($value);
		
	} // _mutateAttribute()
	
	/**
	 * Determine if a set mutator exists for an attribute.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function hasSetMutator($key)
	{
		return method_exists($this, 'set'.studly_case($key).'Attribute');
		
	} // hasSetMutator()
	
	/**
	 * Check if request has succeed
	 *
	 * @return bool
	 */
	public function hasSucceed()
	{
		return (count($this->_errors) == 0 ? true : false);
	
	} // hasSucceed()
	
	/**
	 * Check if error $errorName is present
	 *
	 * @param string $errorName
	 * @return bool
	 */
	public function hasError($errorName)
	{
		if (!empty($this->_errors[$errorName])) {
			return true;
		}
	
		return false;
	
	} // hasError()
	
	/**
	 * Get errors
	 */
	public function getErrors()
	{
		return $this->_errors;
			
	} // getErrors()
	
	/**
	 * Set errors
	 */
	public function setErrors($errors)
	{
		$this->_errors = $errors;
			
	} // getErrors()
	
	/**
	 * Debug the model
	 *
	 * @return array
	 */
	public function debug()
	{
		//Add errores to the debugger
		$this->debug['errors'] = $this->getErrors();
		
		return $this->_debug;
	
	} // getErrors()	
	
	/**
	 * Get created_at as timestamp
	 *
	 * @param mixed $value
	 * @return int
	 */
	public function getCreatedAtTimestamp()
	{
		if (!empty($this->_attributes['created_at'])) {
			return strtotime($this->_attributes['created_at']);
		} else {
			return 0;
		}
	
	} // getCreatedAtTimestamp()
	
	/**
	 * Get updated_at as timestamp
	 *
	 * @param mixed $value
	 * @return int
	 */
	public function getUpdatedAtTimestamp()
	{
		if (!empty($this->_attributes['updated_at'])) {
			return strtotime($this->_attributes['updated_at']);
		} else {
			return 0;
		}
	
	} // getUpdatedAtTimestamp()
	
	/**
	 * Get deleted_at as timestamp
	 *
	 * @param mixed $value
	 * @return int
	 */
	public function getDeletedAtTimestamp()
	{
		if (!empty($this->_attributes['deleted_at'])) {
			return strtotime($this->_attributes['deleted_at']);
		} else {
			return 0;
		}
	
	} // getDeletedAtTimestamp()	
	
	/**
	 * Get model uri
	 *
	 * @return string
	 */
	public function getModelUri()
	{
		return static::$_model_uri;
	
	} // getModelUri()	
	
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
		$this->_debug['status_code'] = $response->getStatusCode();
		$this->_debug['headers'] = $response->getHeaders();
		$this->_debug['content'] = $response->getContent();
		
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
	 * Get the model's original attribute values.
	 *
	 * @param  string|null  $key
	 * @param  mixed  $default
	 * @return array
	 */
	public function getOriginal($key = null, $default = null)
	{
		return array_get($this->original, $key, $default);

	} // getOriginal()
	
	/**
	 * Sync the original attributes with the current.
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function syncOriginal()
	{
		$this->_original = $this->_attributes;
	
		return $this;

	} // syncOriginal()
	
	/**
	 * Dynamically set attributes on the model.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->setAttribute($key, $value);
		
	} // __set()
	
	/**
	 * Dynamically retrieve attributes on the model.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->getAttribute($key);
		
	} // __get()

	/**
	 * Convert the model to its string representation.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toJson();

	} // __toString()
	
	/**
	 * Determine if an attribute exists on the model.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function __isset($key)
	{
		return isset($this->_attributes[$key]);

	} // __isset()
	
	/**
	 * Unset an attribute on the model.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function __unset($key)
	{
		unset($this->_attributes[$key]);

	} // __unset()

	/**
	 * Set results count
	 *
	 * @param int 	$count
	 */
	public function _setCount($count)
	{
		$this->_count = $count;

	} // _setCount()

	/**
	 * Get results count
	 *
	 * @return int 	$count
	 */
	public function getCount()
	{
		return $this->_count;

	} // getCount()
	
	/**
	 * Save the model to the database.
	 */
	public function save()
	{				
		//If the model already exist
		if ($this->getAttribute($this->primary_key)) {			
			
			//Update
			$this->put();	

		//Else create a new model
		} else {
			
			//Create
			$this->post();
		}
		
		//Sync
		$this->syncOriginal();

	} // save()

} // Backenator