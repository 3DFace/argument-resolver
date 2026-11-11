<?php

namespace dface\ArgumentResolver;

use PHPUnit\Framework\TestCase;

class BoolTypeResolverTest extends TestCase
{
	private BoolTypeResolver $resolver;

	protected function setUp() : void
	{
		$this->resolver = new BoolTypeResolver();
	}

	public function testGetName() : void
	{
		$this->assertEquals('bool', $this->resolver->getName());
	}

	public function testStrictResolve() : void
	{
		[$success, $result] = $this->resolver->strictResolve(true);
		$this->assertTrue($success);
		$this->assertTrue($result);

		[$success, $result] = $this->resolver->strictResolve(false);
		$this->assertTrue($success);
		$this->assertFalse($result);

		[$success, $error] = $this->resolver->strictResolve(1);
		$this->assertFalse($success);
		$this->assertEquals('bool expected', $error);

		[$success, $error] = $this->resolver->strictResolve('true');
		$this->assertFalse($success);
		$this->assertEquals('bool expected', $error);

		[$success, $error] = $this->resolver->strictResolve(null);
		$this->assertFalse($success);
		$this->assertEquals('bool expected', $error);
	}

	public function testWeakResolve() : void
	{
		[$success, $result] = $this->resolver->weakResolve(true);
		$this->assertTrue($success);
		$this->assertTrue($result);

		[$success, $result] = $this->resolver->weakResolve(false);
		$this->assertTrue($success);
		$this->assertFalse($result);

		// string booleans
		[$success, $result] = $this->resolver->weakResolve('true');
		$this->assertTrue($success);
		$this->assertTrue($result);

		[$success, $result] = $this->resolver->weakResolve('false');
		$this->assertTrue($success);
		$this->assertFalse($result);

		[$success, $result] = $this->resolver->weakResolve('yes');
		$this->assertTrue($success);
		$this->assertTrue($result);

		[$success, $result] = $this->resolver->weakResolve('no');
		$this->assertTrue($success);
		$this->assertFalse($result);

		[$success, $result] = $this->resolver->weakResolve('on');
		$this->assertTrue($success);
		$this->assertTrue($result);

		[$success, $result] = $this->resolver->weakResolve('off');
		$this->assertTrue($success);
		$this->assertFalse($result);

		// numeric booleans
		[$success, $result] = $this->resolver->weakResolve(1);
		$this->assertTrue($success);
		$this->assertTrue($result);

		[$success, $result] = $this->resolver->weakResolve(0);
		$this->assertTrue($success);
		$this->assertFalse($result);

		[$success, $result] = $this->resolver->weakResolve('1');
		$this->assertTrue($success);
		$this->assertTrue($result);

		[$success, $result] = $this->resolver->weakResolve('0');
		$this->assertTrue($success);
		$this->assertFalse($result);

		// null is false
		[$success, $result] = $this->resolver->weakResolve(null);
		$this->assertTrue($success);
		$this->assertFalse($result);

		// invalid
		[$success, $error] = $this->resolver->weakResolve('abc');
		$this->assertFalse($success);
		$this->assertEquals('bool expected', $error);

		[$success, $error] = $this->resolver->weakResolve(2);
		$this->assertFalse($success);
		$this->assertEquals('bool expected', $error);

	}
}
