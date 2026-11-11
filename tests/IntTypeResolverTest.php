<?php

namespace dface\ArgumentResolver;

use PHPUnit\Framework\TestCase;

class IntTypeResolverTest extends TestCase
{
	private IntTypeResolver $resolver;

	protected function setUp() : void
	{
		$this->resolver = new IntTypeResolver();
	}

	public function testGetName() : void
	{
		$this->assertEquals('int', $this->resolver->getName());
	}

	public function testStrictResolve() : void
	{
		[$success, $result] = $this->resolver->strictResolve(123);
		$this->assertTrue($success);
		$this->assertEquals(123, $result);

		[$success, $error] = $this->resolver->strictResolve('123');
		$this->assertFalse($success);
		$this->assertEquals('int expected, string given', $error);

		[$success, $error] = $this->resolver->strictResolve(123.0);
		$this->assertFalse($success);
		$this->assertEquals('int expected, float given', $error);

		[$success, $error] = $this->resolver->strictResolve('1e1');
		$this->assertFalse($success);
		$this->assertEquals('int expected, string given', $error);

		[$success, $error] = $this->resolver->strictResolve(true);
		$this->assertFalse($success);
		$this->assertEquals('int expected, bool given', $error);

		[$success, $error] = $this->resolver->strictResolve([]);
		$this->assertFalse($success);
		$this->assertEquals('int expected, array given', $error);

		[$success, $error] = $this->resolver->strictResolve(new \stdClass());
		$this->assertFalse($success);
		$this->assertEquals('int expected, object given', $error);
	}

	public function testWeakResolve() : void
	{
		[$success, $result] = $this->resolver->weakResolve(123);
		$this->assertTrue($success);
		$this->assertEquals(123, $result);

		[$success, $result] = $this->resolver->weakResolve('123');
		$this->assertTrue($success);
		$this->assertEquals(123, $result);

		[$success, $result] = $this->resolver->weakResolve(123.0);
		$this->assertTrue($success);
		$this->assertEquals(123, $result);

		[$success, $error] = $this->resolver->strictResolve('1e1');
		$this->assertFalse($success);
		$this->assertEquals('int expected, string given', $error);

		[$success, $error] = $this->resolver->weakResolve(123.5);
		$this->assertFalse($success);
		$this->assertEquals('int expected, float given', $error);

		[$success, $error] = $this->resolver->weakResolve('123.5');
		$this->assertFalse($success);
		$this->assertEquals('int expected, string given', $error);

		[$success, $error] = $this->resolver->weakResolve('abc');
		$this->assertFalse($success);
		$this->assertEquals('int expected, string given', $error);

		[$success, $error] = $this->resolver->weakResolve(true);
		$this->assertFalse($success);
		$this->assertEquals('int expected, bool given', $error);

		[$success, $error] = $this->resolver->weakResolve([]);
		$this->assertFalse($success);
		$this->assertEquals('int expected, array given', $error);

		[$success, $error] = $this->resolver->weakResolve(new \stdClass());
		$this->assertFalse($success);
		$this->assertEquals('int expected, object given', $error);
	}
}
