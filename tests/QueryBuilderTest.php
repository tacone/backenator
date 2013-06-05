<?php

use Mockery as m;
use EllipseSynergie\Backenator;
use Buzz\Message\Response;

/**
 * Test for the Query Builder
 * 
 * @group QueryBuilderTest
 */
class QueryBuilderTest extends PHPUnit_Framework_TestCase {	
	
	/**
	 * Teardown
	 */
	public function tearDown()
	{
		m::close();
	}
	
	public function testBuildUrl()
	{
		//Create the model
		$model = new BackenatorStub();
		$model->setBaseUrl('http://localhost/');
		
		$queryBuilder = new Backenator\Query\BaseBuilder($model);
		$queryBuilder->from($model->getTable());
		
		$this->assertEquals('http://localhost/foo', $queryBuilder->url());
	}
	
	public function testBuildUri()
	{
		//Create the model
		$model = new BackenatorStub;
		$model->segment('maxime')->segment('beaudoin');
		
		$queryBuilder = new Backenator\Query\BaseBuilder($model);
		
		$this->assertEquals('/maxime/beaudoin', $queryBuilder->uri());
	}
	
	public function testGetSucces()
	{		
		//Create the model
		$model = new BackenatorStub(array(), $this->mockSuccess('get'));
		
		$queryBuilder = new Backenator\Query\BaseBuilder($model);
		$result = $queryBuilder->get();
		
		$this->assertEquals('foo', $result->name);
	}
	
	public function testPostSucces()
	{		
		//Create the model
		$model = new BackenatorStub(array(), $this->mockSuccess('post'));
		
		$queryBuilder = new Backenator\Query\BaseBuilder($model);
		$result = $queryBuilder->post(array());
		
		$this->assertTrue($result);
	}
	
	public function testPutSucces()
	{		
		//Create the model
		$model = new BackenatorStub(array(), $this->mockSuccess('put'));
		
		$queryBuilder = new Backenator\Query\BaseBuilder($model);
		$result = $queryBuilder->put(array());
		
		$this->assertTrue($result);
	}
	
	public function testDeleteSucces()
	{		
		//Create the model
		$model = new BackenatorStub(array(), $this->mockSuccess('delete'));
		
		$queryBuilder = new Backenator\Query\BaseBuilder($model);
		$result = $queryBuilder->delete();
		
		$this->assertTrue($result);
	}
	
	public function testHasSuccees()
	{
		//Default object
		$content = new stdClass;
		$content->success = true;
		$queryBuilder = new Backenator\Query\BaseBuilder(new BackenatorStub);
		
		$this->assertTrue($queryBuilder->success($content, m::mock('Buzz\Message\Response')));
	}
	
	public function testHasFail()
	{
		//Default object
		$content = new stdClass;
		$content->success = false;
		$queryBuilder = new Backenator\Query\BaseBuilder(new BackenatorStub);
		
		$this->assertFalse($queryBuilder->success($content, m::mock('Buzz\Message\Response')));
	}
	
	/**
	 * Mock client
	 * 
	 * @param string $method
	 * @return Buzz\Browser
	 */
	public function mockSuccess($method)
	{
		//Mock rest client
		$mock = m::mock('Buzz\Browser');
		
		//Mock rest client response
		$mock_response = m::mock('Buzz\Message\Response');
		
		//Response get content
		$mock_response->shouldReceive('getContent')->once()->andReturn('{"success":true, "results":[{"name":"foo"}]}');
		
		//Rest client get
		$mock->shouldReceive($method)->once()->andReturn($mock_response);
		
		return $mock;
	}	
}