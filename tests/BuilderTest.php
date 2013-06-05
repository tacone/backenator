<?php

use Mockery as m;
use EllipseSynergie\Backenator;
use EllipseSynergie\Backenator\Builder;
use EllipseSynergie\Backenator\Query\Builder as QueryBuilder;
use Buzz\Message\Response;

/**
 * Test for builder
 * 
 * @author Ellipse Synergie <support@ellipse-synergie.com>
 * @group BuilderTest
 */
class BuilderTest extends PHPUnit_Framework_TestCase {	
	
	/**
	 * Teardown
	 */
	public function tearDown()
	{
		m::close();
	}
	
	public function testSetModel()
	{
		$mock = m::mock('EllipseSynergie\Backenator\Query\Builder');
		$mock->shouldReceive('from')->once()->andReturn(true);
		
		$builder = new Builder($mock);
		
		$result = $builder->setModel(new BackenatorStub);
		
		$this->assertInstanceOf('EllipseSynergie\Backenator\Builder', $result);
	}
	
	public function testGet()
	{
		$builder = new Builder($this->mockBuilder('get'));
		
		$this->assertTrue($builder->get());
	}
	
	public function testInsert()
	{		
		$builder = new Builder($this->mockBuilder('post'));
		
		$this->assertTrue($builder->insert(array()));
	}
	
	public function testInsertGetId()
	{
		$builder = new Builder($this->mockBuilder('post'));
		
		$this->assertTrue($builder->insertGetId(array()));
	}
	
	public function testUpdate()
	{
		$builder = new Builder($this->mockBuilder('put'));
		
		$this->assertTrue($builder->update(array()));
	}
	
	public function testDelete()
	{
		$builder = new Builder($this->mockBuilder('delete'));
		
		$this->assertTrue($builder->delete());
	}
	
	public function mockBuilder($method)
	{
		$mock = m::mock('EllipseSynergie\Backenator\Query\Builder');
		$mock->shouldReceive($method)->once()->andReturn(true);
		$mock->shouldReceive('getResponse')->once()->andReturn(m::mock('Buzz\Message\Response'));
		
		return $mock;
	}
}