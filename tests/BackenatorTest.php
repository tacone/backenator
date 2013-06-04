<?php

use Mockery as m;
use EllipseSynergie\Backenator;
use Buzz\Message\Response;

/**
 * Test for the Classrom model
 * 
 * @author Ellipse Synergie <support@ellipse-synergie.com>
 * @group BackenatorTest
 */
class BackenatorTest extends PHPUnit_Framework_TestCase {	
	
	/**
	 * Teardown
	 */
	public function tearDown()
	{
		m::close();
	}

	/**
	 * Setup the test
	 */
	public function setUp()
	{
	}
	
	public function testUrlWithSegment()
	{
		//Create the model
		$model = new BackenatorStub;
		
		//Add segment
		$model->segment('maxime')->segment('beaudoin');
		
		$this->assertEquals('foo/maxime/beaudoin', $model->url());
	}
	
	public function testUrlWithWhere()
	{
		//Create the model
		$model = new BackenatorStub;
		
		//Add segment
		$model->where('user', 'maxime')->where('date', 'today');
		
		$this->assertEquals('foo?user=maxime&date=today', $model->url());
	}
	
	public function testUrlWithtWhereAndSegment()
	{
		//Create the model
		$model = new BackenatorStub;
		
		//Add segment
		$model->segment('maxime')->segment('beaudoin')->where('user', 'maxime')->where('date', 'today');
		
		$this->assertEquals('foo/maxime/beaudoin?user=maxime&date=today', $model->url());
	}
	
	
	public function testGetSuccess()
	{
		//Mock rest client
		$mock = m::mock('Buzz\Browser');
		
		//Mock rest client response
		$mock_response = m::mock('Buzz\Message\Response');
		
		//Response get content
		$mock_response->shouldReceive('getContent')->twice()->andReturn('{"success":true,"results":[{"name":"foo"}]}');
		
		//Rest client get
		$mock->shouldReceive('get')->once()->andReturn($mock_response);
		
		//Create the model
		$model = new BackenatorStub(array(), $mock);
		
		//Try to get classrooms
		$model = $model->where('name', 'foo')->get();
		
		$this->assertEquals('foo', $model->name);
	}
	
	public function testGetFail()
	{
		//Mock rest client
		$mock = m::mock('Buzz\Browser');
		
		//Mock rest client response
		$mock_response = m::mock('Buzz\Message\Response');
		
		//Response get content
		$mock_response->shouldReceive('getContent')->times(2)->andReturn('{"success":false}');
		
		//Rest client get
		$mock->shouldReceive('get')->once()->andReturn($mock_response);
		
		//Create the model
		$model = new BackenatorStub(array(), $mock);
		
		//Try to get classrooms
		$model = $model->where('foo', 'bar')->get();
		
		$this->assertFalse($model);
	}
	
	public function testPostSuccess()
	{
		//Mock rest client
		$mock = m::mock('Buzz\Browser');
		
		//Mock rest client response
		$mock_response = m::mock('Buzz\Message\Response');
		
		//Rest client get
		$mock->shouldReceive('post')->once()->andReturn($mock_response);
		
		//Response get content
		$mock_response->shouldReceive('getContent')->twice()->andReturn('{"success":true,"id":1,"created_at":"' . date('Y-m-d H:i:s') . '"}');
		
		//Create the model
		$model = new BackenatorStub(array('name' => 'bar'), $mock);
		
		//Post the classroom
		$status = $model->post();
		
		//Assertions
		$this->assertTrue($status);
		$this->assertEquals(1, $model->id);
		$this->assertEquals('bar', $model->name);
	}
	
	public function testPostFail()
	{
		//Mock rest client
		$mock = m::mock('Buzz\Browser');
		
		//Mock rest client response
		$mock_response = m::mock('Buzz\Message\Response');
		
		//Rest client get
		$mock->shouldReceive('post')->once()->andReturn($mock_response);
		
		//Response get content
		$mock_response->shouldReceive('getContent')->times(2)->andReturn('{"success":false}');
		
		//Create the model
		$model = new BackenatorStub(array(), $mock);
		
		//Post the classroom
		$status = $model->post();
		
		//Assertions
		$this->assertFalse($status);
	}
	
	public function testPutSuccess()
	{
		
	}
	
	public function testPutFail()
	{
		
	}
	
	public function testDeleteSuccess()
	{
		
	}
	
	public function testDeleteFail()
	{
		
	}
	
	/*
	public function testFireErrorsWithHttp401()
	{
		//Mock rest client
		$mock = m::mock('Buzz\Browser');
		
		//Mock rest client response
		$mock_response = m::mock('Buzz\Message\Response');
		
		//Response get content
		$mock_response->shouldReceive('getContent')->twice()->andReturn('{"uri":null,"success":false,"errors":[{"E_GENERIC_INVALID_TOKEN":"You should have a valid token."}]}');	
		$mock_response->shouldReceive('getStatusCode')->twice()->andReturn(401);	
		$mock_response->shouldReceive('getHeaders')->once()->andReturn(array('HTTP/1.0 401 Unauthorized'));		
		
		//Create the model
		$classroom = new BackenatorStub(array(), $mock);
		$classroom->clientErrors($mock_response);
	}
	*/
	
}

class BackenatorStub extends Backenator {
	
	protected $table = 'foo';
	protected $fillable = array('name');
}