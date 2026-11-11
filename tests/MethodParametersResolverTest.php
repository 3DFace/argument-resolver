<?php

namespace dface\ArgumentResolver;

use PHPUnit\Framework\TestCase;
use stdClass;

class MethodParametersResolverTest extends TestCase
{
	private TypeResolverFactory $resolverFactory;

	protected function setUp() : void
	{
		$builtin_resolvers = [
			'int' => new IntTypeResolver(),
			'float' => new FloatTypeResolver(),
			'bool' => new BoolTypeResolver(),
			'string' => new StringTypeResolver(),
			'array' => new ArrayTypeResolver(),
			'object' => new ObjectTypeResolver(),
		];
		$this->resolverFactory = new TypeResolverFactory(
			$builtin_resolvers,
			new MixedTypeResolver(),
			new NullTypeResolver(),
		);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testResolveForSimpleParameters() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'simpleParameters');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [42, 'test', true, [1, 2]];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		$this->assertEquals([42, 'test', true, [1, 2]], $result);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testInvalidDataType() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'simpleParameters');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [new \stdClass(), new \stdClass(), new \stdClass(), 1];
		[$success, $errors] = $resolver->weakResolve($rawData);

		$this->assertFalse($success);
		$this->assertStringContainsString("'param1': int expected", $errors);
		$this->assertStringContainsString("'param2': string expected", $errors);
		$this->assertStringContainsString("'param3': bool expected", $errors);
		$this->assertStringContainsString("'param4': array expected", $errors);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testResolveForSimpleParametersAssocWeak() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'simpleParameters');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = ['param1' => "42", 'param2' => 1, 'param3' => 1, 'param4' => [1]];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		$this->assertEquals(['param1' => 42, 'param2' => '1', 'param3' => true, 'param4' => [1]], $result);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testResolveWithDefaultValues() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'parametersWithDefaults');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [123];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		$this->assertEquals([123, 'default', '1'], $result);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testResolveWithVariadicParameters() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'variadicParameters');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = ["1.39", "2", 3, "4"];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		$this->assertEquals([1.39, 2, 3, 4], $result);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testResolveWithVariadicParametersAssoc() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'variadicParameters');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = ["param1" => 1, "variadic" => [1]];
		[$success, $errors] = $resolver->weakResolve($rawData);

		$this->assertFalse($success);
		$this->assertStringContainsString("cant use variadic parameters with associative arguments", $errors);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testResolveWithMissingMandatoryParameters() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'parametersWithDefaults');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [];
		[$success, $errors] = $resolver->weakResolve($rawData);

		$this->assertFalse($success);
		$this->assertStringContainsString("'param1': is mandatory", $errors);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testEnvelopeParameter() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'envelopeParameter');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = ["test"];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		$this->assertInstanceOf(TestEnvelope::class, $result[0]);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testEnvelopeParameterFailure() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'envelopeParameter');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [[1]];
		[$success, $errors] = $resolver->weakResolve($rawData);

		$this->assertFalse($success);
		$this->assertStringContainsString("'param1': string expected", $errors);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testDtoParametersList() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'dtoParameter');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [["test", '1', 1, 'test@test.com']];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		self::assertEquals(new TestDto("test", 1, true, new TestEmail('test@test.com')), $result[0]);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testDtoParameterAssoc() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'dtoParameter');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [["field3" => 1, "field1" => "test", "field2" => '1', "field4" => 'test@test.com']];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		self::assertEquals(new TestDto("test", 1, true, new TestEmail('test@test.com')), $result[0]);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testDtoParameterObj() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'dtoParameter');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$obj = new stdClass();
		$obj->field1 = "test";
		$obj->field2 = 1;
		$obj->field3 = true;
		$obj->field4 = 'test@test.com';
		$rawData = [$obj];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		self::assertEquals(new TestDto("test", 1, true, new TestEmail('test@test.com')), $result[0]);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testDtoParameterObjExceptionInConstructor() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'dtoParameter');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$obj = new stdClass();
		$obj->field1 = "test";
		$obj->field2 = 1000;
		$obj->field3 = true;
		$obj->field4 = 'test@test.com';
		$rawData = [$obj];
		[$success, $errors] = $resolver->weakResolve($rawData);

		$this->assertFalse($success);
		$this->assertStringContainsString("'param1': 'field2' must be less than 100", $errors);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testDtoDeserialize() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'dtoParameterDeserialize');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [["prop3" => 1, "prop1" => "test", "prop2" => '1', "prop4" => 'test@test.com']];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		self::assertEquals(new TestDtoDeserialize("test", 1, true, new TestEmail('test@test.com')), $result[0]);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testDtoDeserializeTypeErrorInConstructorCall() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'dtoParameterDeserialize');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [["prop3" => 1, "prop1" => null, "prop2" => '1', "prop4" => 'test@test.com']];
		[$success, $errors] = $resolver->weakResolve($rawData);

		$this->assertFalse($success);
		$this->assertStringContainsString("'param1': __construct(): Argument #1 (\$field1) must be of type string, null given", $errors);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testDtoDeserializeExceptionInConstructor() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'dtoParameterDeserialize');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [["prop3" => 1, "prop1" => "test", "prop2" => '1000', "prop4" => 'test@test.com']];
		[$success, $errors] = $resolver->weakResolve($rawData);

		$this->assertFalse($success);
		$this->assertStringContainsString("'param1': 'field2' must be less than 100", $errors);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testDtoDeserializeAssoc() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'dtoParameterDeserializeAssoc');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [["prop3" => 1, "prop1" => "test", "prop2" => '1', "prop4" => 'test@test.com']];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		self::assertEquals(new TestDtoDeserializeAssoc("test", 1, true, new TestEmail('test@test.com')), $result[0]);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testDtoDeserializeAssocTypeErrorInConstructorCall() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'dtoParameterDeserializeAssoc');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [["prop3" => 1, "prop1" => null, "prop2" => '1', "prop4" => 'test@test.com']];
		[$success, $errors] = $resolver->weakResolve($rawData);

		$this->assertFalse($success);
		$this->assertStringContainsString("'param1': __construct(): Argument #1 (\$field1) must be of type string, null given", $errors);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testDtoParameterFailure1() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'dtoParameter');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = ["test"];
		[$success, $errors] = $resolver->weakResolve($rawData);

		$this->assertFalse($success);
		$this->assertStringContainsString("'param1': [field1:string, field2:int, field3:bool, field4:string] expected", $errors);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testDtoParameterFailure2() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'dtoParameter');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [["field3" => 1, "field1" => "test", "field2" => '1.1']];
		[$success, $errors] = $resolver->weakResolve($rawData);

		$this->assertFalse($success);
		$this->assertStringContainsString("'param1': ['field2': int expected, 'field4': is mandatory]", $errors);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testDtoParameterFailure3() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'dtoParameter');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [["field3" => 1, "field1" => "test", "field2" => '1', 'field4' => 'test']];
		[$success, $errors] = $resolver->weakResolve($rawData);

		$this->assertFalse($success);
		$this->assertStringContainsString("'param1': 'field4': Invalid email address", $errors);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testDtoNestParameterAssoc() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'dtoNestParameter');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [
			[
				'field1' => ["test", '1', 1, 'test@test.com'],
				'field2' => "asd",
				'field3' => true,
			],
			null
		];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		$expected = new TestDtoNest(new TestDto("test", 1, true, new TestEmail('test@test.com')), new TestEnvelope("asd"), true);
		self::assertEquals([$expected, null], $result);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testDtoNestParameterAssocEmailFailure() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'dtoNestParameter');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [
			[
				'field1' => ["test", '1', 1, 'test'],
				'field2' => "asd",
				'field3' => true,
			],
			null
		];
		[$success, $errors] = $resolver->weakResolve($rawData);

		$this->assertFalse($success);
		$this->assertStringContainsString("'param1': 'field1': 'field4': Invalid email address", $errors);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testUnionParameter1() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'unionParameter');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = ["test"];
		[$success, $result] = $resolver->weakResolve($rawData);

		/** @var TestEnvelope $arg */
		$arg = $result[0];
		$this->assertTrue($success);
		$this->assertInstanceOf(TestEnvelope::class, $arg);
		$this->assertEquals("test", $arg->x);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testUnionParameter2() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'unionParameter');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [1];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		$this->assertEquals(1, $result[0]);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testUnionParameter3() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'unionParameter');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [null];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		$this->assertEquals(null, $result[0]);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testUnionParameterFailure() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'unionParameter');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [[]];
		[$success, $errors] = $resolver->weakResolve($rawData);

		$this->assertFalse($success);
		$this->assertStringContainsString("'param1': string|int|null expected", $errors);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testUnionParameterBothStrings1() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'unionParameterAllStrings');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = ['test@test.com'];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		$this->assertEquals(new TestEmail('test@test.com'), $result[0]);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testUnionParameterBothStrings2() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'unionParameterAllStrings');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = ['380675090909'];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		$this->assertEquals(new TestPhone('380675090909'), $result[0]);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testUnionParameterBothStrings3() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'unionParameterAllStrings');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [123];
		[$success, $result] = $resolver->weakResolve($rawData);

		$this->assertTrue($success);
		$this->assertEquals('123', $result[0]);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testUnionParameterBothStringsFailure() : void
	{
		$method = new \ReflectionMethod(TestApiService::class, 'unionParameterAllStrings');
		$resolver = $this->resolverFactory->createMethodResolver($method);

		$rawData = [[]];
		[$success, $errors] = $resolver->weakResolve($rawData);

		$this->assertFalse($success);
		$this->assertStringContainsString("'param1': string expected", $errors);
	}

}
