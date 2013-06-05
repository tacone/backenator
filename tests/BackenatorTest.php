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
	
	public function testClientFactory()
	{
		$model = new BackenatorStub;
		$model->clientFactory(m::mock('Buzz\Browser'));
		
		$this->assertInstanceOf('Buzz\Browser', $model->getClient());
	}

	public function testGet()
	{
		$model = new BackenatorStub;
		$model->clientFactory($this->mockSuccess('get'));
		
		$this->assertInstanceOf('EllipseSynergie\Backenator', $model->get());
	}
	
	public function testFirst()
	{			
		$model = new BackenatorStub;
		$model->clientFactory($this->mockSuccess('get'));
		
		$this->assertInstanceOf('EllipseSynergie\Backenator', $model->first());
	}
	
	/*
	public function testFind()
	{
		$model = new BackenatorStub;
		$model->clientFactory($this->mockSuccess('get'));
	
		$this->assertInstanceOf('EllipseSynergie\Backenator', $model->find(1));
	}*/
	
	public function testWhere()
	{
		$model = new BackenatorStub;
		$model->where('foo', 'bar');
		
		$this->assertEquals(array('foo' => 'bar'), $model->getParams());
	}
	
	public function testSegment()
	{
		$model = new BackenatorStub;
		$model->segment('foo')->segment('bar');
		
		$this->assertEquals(array('foo', 'bar'), $model->getSegments());
	}
	
	public function testNewQuery()
	{
		$model = new BackenatorStub;
		$query = $model->newQuery();
		
		$this->assertInstanceOf('EllipseSynergie\Backenator\Builder', $query);
	}
	
	public function mockSuccess($method)
	{
		$mock = m::mock('Buzz\Browser');
		
		//Mock rest client response
		$mock_response = m::mock('Buzz\Message\Response');
		
		//Response get content
		$mock_response->shouldReceive('getContent')->times(3)->andReturn('{"success":true, "results":[{"name":"foo"}]}');
		$mock_response->shouldReceive('isSuccessful')->once()->andReturn(true);
		
		//Rest client get
		$mock->shouldReceive($method)->once()->andReturn($mock_response);
		
		return $mock;
	}
}

class BackenatorStub extends Backenator {
	
	protected $table = 'foo';
	protected $fillable = array('name');
	#public $incrementing = false;
}