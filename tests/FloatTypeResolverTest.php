<?php

namespace dface\ArgumentResolver;

use PHPUnit\Framework\TestCase;

class FloatTypeResolverTest extends TestCase
{
	private FloatTypeResolver $resolver;

	protected function setUp() : void
	{
		$this->resolver = new FloatTypeResolver();
	}

	public function testGetName() : void
	{
		$this->assertEquals('float', $this->resolver->getName());
	}

	public function testStrictResolve() : void
	{
		[$success, $result] = $this->resolver->strictResolve(1.23);
		$this->assertTrue($success);
		$this->assertEquals(1.23, $result);

		[$success, $result] = $this->resolver->strictResolve(1);
		$this->assertTrue($success);
		$this->assertEquals(1, $result);

		[$success, $error] = $this->resolver->strictResolve('1.23');
		$this->assertFalse($success);
		$this->assertEquals('float expected', $error);
	}

	public function testWeakResolve() : void
	{
		[$success, $result] = $this->resolver->weakResolve(1.23);
		$this->assertTrue($success);
		$this->assertEquals(1.23, $result);

		[$success, $result] = $this->resolver->weakResolve(123);
		$this->assertTrue($success);
		$this->assertEquals(123.0, $result);

		[$success, $result] = $this->resolver->weakResolve('1.23');
		$this->assertTrue($success);
		$this->assertEquals(1.23, $result);

		[$success, $result] = $this->resolver->weakResolve('123');
		$this->assertTrue($success);
		$this->assertEquals(123.0, $result);

		[$success, $result] = $this->resolver->weakResolve('1e2');
		$this->assertTrue($success);
		$this->assertEquals(100.0, $result);

		[$success, $error] = $this->resolver->weakResolve('abc');
		$this->assertFalse($success);
		$this->assertEquals('float expected', $error);

		[$success, $error] = $this->resolver->weakResolve(true);
		$this->assertFalse($success);
		$this->assertEquals('float expected', $error);

		[$success, $error] = $this->resolver->weakResolve(null);
		$this->assertFalse($success);
		$this->assertEquals('float expected', $error);
	}
}
