<?php

use Mockery as m;
use EllipseSynergie\Backenator;

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
		//Setup from Laravel
		parent::setUp();	

		//Bind stub to model
		App::bind('Event', function() { return new BackenatorTest_Event; });
	}
	
	public function testUnsetAttribute()
	{		
		$model = new BackenatorStub;
		$model->foo = 'bar';
		
		$this->assertTrue(isset($model->foo));
		unset($model->foo);
		$this->assertFalse(isset($model->foo));
	}
	
	public function testIsSetAttribute()
	{
		$model = new BackenatorStub;
		$model->foo = 'bar';
		
		$this->assertTrue(empty($model->id));
		$this->assertFalse(empty($model->foo));
	}
	
	public function testIsEmptyAttribute()
	{
		$model = new BackenatorStub;
		$model->foo = 'bar';
		
		$this->assertFalse(isset($model->id));
		$this->assertTrue(isset($model->foo));
	}
	
	public function testToArray()
	{
		$model = new BackenatorStub;
		$model->foo = 'bar';		

		$data = $model->toArray();
		$this->assertIsArray($data);		
	}
	
	public function testToJson()
	{
		$model = new BackenatorStub;
		$model->foo = 'bar';
	
		$data = $model->toJson();
		$this->assertIsString($data);
	}
	
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
	

	public function testFindQuery()
	{
		//Mock rest client
		$mock = m::mock('Buzz\Browser');
		
		//Mock rest client response
		$mock_response = m::mock('Buzz\Message\Response');
		
		//Response get content
		$mock_response->shouldReceive('getContent')->twice()->andReturn('{"success":true,"results":[{"id":1}]}');		
		
		//Rest client get
		$mock->shouldReceive('get')->once()->andReturn($mock_response);
		
		//Create the model
		$model = new BackenatorStub(array(), $mock);
		
		//Find the user
		$model->findQuery(1);		
		
		//Assert
		$this->assertEquals(1, $model->id);
	}


	public function testAccesors()
	{
		//Mock rest client
		$mock = m::mock('Buzz\Browser');
	
		//Mock rest client response
		$mock_response = m::mock('Buzz\Message\Response');
	
		//Response get content
		$mock_response->shouldReceive('getContent')->twice()->andReturn('{"success":true,"results":[{"id":1,"metadata": {"points": 7,"weighting": 25}}]}');
	
		//Rest client get
		$mock->shouldReceive('get')->once()->andReturn($mock_response);
	
		//Create the model
		$model = new BackenatorStub(array(), $mock);
	
		//Find the user
		$model->findQuery(1);
	
		//Assetions
		$this->assertTrue(is_object($model->metadata));
		$this->assertEquals(25, $model->metadata->weighting);
		$this->assertEquals(7, $model->metadata->points);
	}
	

	public function testPost()
	{
		//Mock rest client
		$mock = m::mock('Buzz\Browser');
		
		//Mock rest client response
		$mock_response = m::mock('Buzz\Message\Response');
		
		//Rest client get
		$mock->shouldReceive('post')->once()->andReturn($mock_response);
		
		//Response get content
		$mock_response->shouldReceive('getContent')->twice()->andReturn('{"success":true,"id":1,"created_at":"Thu, 28 Mar 2013 20:39:19 +0000"}');	
		
		//Set data
		$data = array(
			'name' => 'Français secondaire 4',
			'category' => 'french',
			'teacher_id' => 1,
			'place_id' => 1,
		);
		
		//Create the model
		$model = new BackenatorStub($data, $mock);
		
		//Post the classroom
		$model->post();
		
		//Assertions
		$this->assertEquals(1, $model->id);
		$this->assertEquals('Thu, 28 Mar 2013 20:39:19 +0000', $model->created_at);
		$this->assertEquals($data['name'], $model->name);
		
	}
	

	public function testSave()
	{
		//Mock rest client
		$mock = m::mock('Buzz\Browser');
		
		//Mock rest client response
		$mock_response = m::mock('Buzz\Message\Response');
		
		//Rest client get
		$mock->shouldReceive('post')->once()->andReturn($mock_response);
		
		//Response get content
		$mock_response->shouldReceive('getContent')->twice()->andReturn('{"success":true,"id":1,"created_at":"Thu, 28 Mar 2013 20:39:19 +0000"}');	
		
		//Set data
		$data = array(
			'name' => 'Français secondaire 4',
			'category' => 'french',
			'teacher_id' => 1,
			'place_id' => 1,
		);
		
		//Create the model
		$model = new BackenatorStub($data, $mock);
		
		//Post the classroom
		$model->save();
		
		//Assertions
		$this->assertEquals(1, $model->id);
		$this->assertEquals('Thu, 28 Mar 2013 20:39:19 +0000', $model->created_at);
		$this->assertEquals($data['name'], $model->name);
		
	}
	

	public function testGet()
	{		
		//Mock rest client
		$mock = m::mock('Buzz\Browser');
		
		//Mock rest client response
		$mock_response = m::mock('Buzz\Message\Response');
		
		//Response get content
		$mock_response->shouldReceive('getContent')->twice()->andReturn('{"success":true,"results":[{"id":1,"metadata": {"points": 7,"weighting": 25}}]}');
		
		//Rest client get
		$mock->shouldReceive('get')->once()->andReturn($mock_response);
		
		//Create the model
		$model = new BackenatorStub(array(), $mock);
		
		//Try to get classrooms
		$model = $model->where('user_id', 3)->get();
		
		$this->assertEquals(25, $model[0]->metadata->weighting);
		$this->assertEquals(7, $model[0]->metadata->points);
	}	
	
	public function testFirst()
	{
		//Mock rest client
		$mock = m::mock('Buzz\Browser');
		
		//Mock rest client response
		$mock_response = m::mock('Buzz\Message\Response');
		
		//Response get content
		$mock_response->shouldReceive('getContent')->twice()->andReturn('{"success":true,"results":[{"id":1,"metadata": {"points": 7,"weighting": 25}}]}');
		
		//Rest client get
		$mock->shouldReceive('get')->once()->andReturn($mock_response);
		
		//Create the model
		$model = new BackenatorStub(array(), $mock);
		
		//Try to get classrooms
		$model = $model->where('user_id', 3)->first();
		
		$this->assertEquals(25, $model->metadata->weighting);
		$this->assertEquals(7, $model->metadata->points);
	}
	

	public function testDelete()
	{
		
		//Mock rest client
		$mock = m::mock('Buzz\Browser');
		
		//Mock rest client response
		$mock_get_response = m::mock('Buzz\Message\Response');
		$mock_delete_response = m::mock('Buzz\Message\Response');
		
		//Response get content
		$mock_get_response->shouldReceive('getContent')->twice()->andReturn('{"success":true,"results":[{"id":1,"metadata": {"points": 7,"weighting": 25}}]}');	
		$mock_delete_response->shouldReceive('getContent')->twice()->andReturn('{"success":true,"deleted_at":"Wed, 03 Apr 2013 18:45:01 +0000"}');		
		
		//Rest client get
		$mock->shouldReceive('get')->once()->andReturn($mock_get_response);
		$mock->shouldReceive('delete')->once()->andReturn($mock_delete_response);
		
		//Create the model
		$model = new BackenatorStub(array(), $mock);
		
		//Find the user
		$model->findQuery(1);
		
		//Delete the user
		$model->delete();
		
	}
	
	public function testBuildRequestUrl()
	{
		//Create the model
		$model = new BackenatorStub;
		
		//Add segment
		$model->addUriSegment('test')->where('foo', 'bar')->where('bar', 'foo');
		
		$this->assertEquals(Config::get('app.backend.uri') . 'foo/test?foo=bar&bar=foo', $model->buildRequestUrl());
	}
	
	public function testIsFillable()
	{
		//Create the model
		$model = new BackenatorStub;
		
		$this->assertFalse($model->isFillable('foo'));
		$this->assertTrue($model->isFillable('bar'));
		$this->assertFalse($model->isFillable('test'));
	}
	
	public function testIsGuarded()
	{
		//Create the model
		$model = new BackenatorStub;
		
		$this->assertTrue($model->isGuarded('foo'));
		$this->assertFalse($model->isGuarded('bar'));
		$this->assertFalse($model->isGuarded('test'));
	}
	
	public function testIsTotallyGuarded()
	{
		//Create the model
		$model = new BackenatorStubTotallyGuarded;
		
		$this->assertTrue($model->totallyGuarded());
	}
	
	public function testHasMutator()
	{
		//Create the model
		$model = new BackenatorStubHasMutator;
		
		$this->assertTrue($model->hasSetMutator('foo'));		
		$this->assertTrue($model->hasGetMutator('foo'));
		
		$this->assertFalse($model->hasSetMutator('bar'));		
		$this->assertFalse($model->hasGetMutator('bar'));
	}
	
}

class BackenatorStub extends Backenator {
	
	protected static $_model_uri = 'foo'; 
	
	public $_guarded = array(
		'foo'
	);
	
	public $_fillable = array(
		'bar'
	);	
}

class BackenatorStubTotallyGuarded extends Backenator {

	public $_guarded = array(
		'*'
	);
}

class BackenatorStubHasMutator extends Backenator {
	
	public function getFooAttribute($value)
	{
		return ucfirst($value);
	}
	
	public function setFooAttribute($value)
	{
		$this->attributes['foo'] = strtolower($value);
	}
}

class BackenatorTest_Event {
	
}