<?php 
namespace EllipseSynergie\Backenator\Query;

use EllipseSynergie\Backenator;

/**
 * Query builder class
 *
 */
abstract class Builder implements BuilderInterface {

	/**
	 * The model object
	 * 
	 * @var EllipseSynergie\Backenator
	 */
	protected $model;
	
	/**
	 * The client response
	 * 
	 * @var Buzz\Message\Response
	 */
	protected $response;
	
	/**
	 * The table from 
	 * 
	 * @var string
	 */
	protected $from;
	
	/**
	 * Constructor
	 *
	 * @var EllipseSynergie\Backenator
	 */
	public function __construct(Backenator $model)
	{
		$this->model = $model;
	}
	
	/**
	 * GET method
	 *
	 * @return Backenator|array
	 */
	public function get()
	{								
		//Build the url
		$url = $this->url();
		
		//Do the get resquest to the Backend
		$response = $this->model->getClient()->get($url);
		
		//Convert response to json
		$content = json_decode($response->getContent());
		
		//Handle the get method
		$result = $this->success($content, $response);
		
		//If we have a result
		if(!empty($result)){				
			$result = $this->buildResults($content);
		}
		
		//Set the request response
		$this->setResponse($response);
		
		return $result;		
	}
	
	/**
	 * POST method.
	 *
	 * @param  array  $attributes
	 */
	public function post(array $attributes)
	{				
		//Build the url
		$url = $this->url();
		
		//Build data query
		$data = http_build_query($attributes);
		
		//Do the get resquest to the Backend
		$response = $this->model->getClient()->post($url, array(), $data);
		
		//Convert response to json
		$content = json_decode($response->getContent());
		
		//Handle the post method
		$result = $this->success($content, $response);
		
		//Set the request response
		$this->setResponse($response);
		
		return $result;		
	}
	
	/**
	 * PUT method
	 *
	 * @return bool;
	 */
	public function put(array $attributes)
	{				
		//Build the url
		$url = $this->url();
		
		//Build data query
		$data = http_build_query($attributes);
		
		//Do the get resquest to the Backend
		$response = $this->model->getClient()->put($url, array(), $data);
		
		//Convert response to json
		$content = json_decode($response->getContent());
		
		//Handle the put method
		$result = $this->success($content, $response);
		
		//Set the request response
		$this->setResponse($response);
		
		return $result;		
	}
	
	/**
	 * DELETE method
	 *
	 * @return bool;
	 */
	public function delete()
	{						
		//Build the url
		$url = $this->url();
		
		//Do the get resquest to the Backend
		$response = $this->model->getClient()->delete($url, array());

		//Convert response to json
		$content = json_decode($response->getContent());
		
		//Handle the delete method
		$result = $this->success($content, $response);
		
		//Set the request response
		$this->setResponse($response);	

		return $result;
	}
	
	/**
	 * Build the request URL
	 *
	 * @return string
	 */
	public function url()
	{
		//Build the url
		$url = $this->model->getBaseUrl() . $this->uri();
	
		//If we have parameters
		if($this->model->getParams()){
	
			//Add query string parameters to the URL
			$url .= '?' . http_build_query($this->model->getParams());
				
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
		$uri = $this->from . '/';
	
		//If we have segment to add
		if($this->model->getSegments())
		{
			//for each segment to add
			foreach ($this->model->getSegments() as $segment)
			{
				//Add the segment to the final URI
				$uri .= $segment . '/';
			}
		}
	
		//Return the uri and remove the latest trailing slash
		return substr($uri, 0, -1);
	}	
	
	/**
	 * Set the table which the query is targeting.
	 *
	 * @param  string  $table
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function from($table)
	{
		$this->from = $table;
	
		return $this;
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