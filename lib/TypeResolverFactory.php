<?php

namespace dface\ArgumentResolver;

readonly class TypeResolverFactory
{

	public function __construct(
		private array $builtin_resolvers,
		private TypeResolver $mixedTypeResolver,
		private TypeResolver $nullTypeResolver,
		private string $deserialize_method_name = 'deserialize',
	) {
	}

	public function createBuiltinResolver(string $type_name) : TypeResolver
	{
		return $this->builtin_resolvers[$type_name] ?? throw new \InvalidArgumentException("Unsupported builtin type: $type_name");
	}

	/**
	 * @throws \ReflectionException
	 */
	public function createMethodResolver(\ReflectionMethod $method) : MethodParametersResolver
	{
		$parameters = $method->getParameters();
		$parameters_resolvers = [];
		$has_variadic = false;
		foreach ($parameters as $param) {
			$parameters_resolvers[] = [$param, $this->createParameterResolver($param)];
			if (!$has_variadic && $param->isVariadic()) {
				$has_variadic = true;
			}
		}
		return new MethodParametersResolver($parameters_resolvers, $has_variadic);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function createParameterResolver(\ReflectionParameter $parameter) : TypeResolver
	{
		if (!$parameter->hasType()) {
			return $this->mixedTypeResolver;
		}
		$type = $parameter->getType();
		if ($type instanceof \ReflectionNamedType) {
			return $this->createNamedTypeResolver($type);
		}
		if ($type instanceof \ReflectionUnionType) {
			return $this->createUnionTypeResolver($type);
		}
		if ($type instanceof \ReflectionIntersectionType) {
			throw new \InvalidArgumentException("Intersection parameter type is not supported");
		}
		throw new \InvalidArgumentException("Unsupported parameter type: ".\get_class($type));
	}

	/**
	 * @throws \ReflectionException
	 */
	public function createUnionTypeResolver(\ReflectionUnionType $type) : UnionTypeResolver
	{
		$subtypes_resolvers = [];
		foreach ($type->getTypes() as $subtype) {
			$subtypes_resolvers[] = $this->createNamedTypeResolver($subtype);
		}
		return new UnionTypeResolver($subtypes_resolvers);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function createNamedTypeResolver(\ReflectionNamedType $type) : TypeResolver
	{
		$type_name = $type->getName();
		if ($type_name === 'mixed') {
			return $this->mixedTypeResolver;
		}
		if ($type_name === 'null') {
			return $this->nullTypeResolver;
		}
		if ($type->isBuiltin()) {
			$resolver = $this->createBuiltinResolver($type_name);
		} elseif (\class_exists($type_name)) {
			$resolver = $this->createClassTypeResolver($type_name);
		} else {
			throw new \InvalidArgumentException("Type $type_name not found");
		}
		if ($type->allowsNull()) {
			$resolver = new NullableTypeResolver($resolver);
		}
		return $resolver;
	}

	/**
	 * @throws \ReflectionException
	 */
	public function createClassTypeResolver(string $class_name) : TypeResolver
	{
		$class = new \ReflectionClass($class_name);

		if ($class->hasMethod($this->deserialize_method_name)) {
			return $this->createFactoryMethodClassTypeResolver($class, $this->deserialize_method_name);
		}

		return $this->createConstructorClassTypeResolver($class);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function createConstructorClassTypeResolver(\ReflectionClass $class) : TypeResolver
	{
		$constructor = $class->getConstructor();

		if (!$constructor->isPublic()) {
			throw new \InvalidArgumentException("Constructor must be public");
		}

		$params_count = $constructor->getNumberOfParameters();

		if ($params_count === 1) {
			$param = $constructor->getParameters()[0];
			$param_resolver = $this->createNamedTypeResolver($param->getType());
			$factory = fn($arg) => $class->newInstance($arg);
			return new EnvelopeClassTypeResolver($factory, $param_resolver);
		}

		$methodParametersResolver = $this->createMethodResolver($constructor);
		$factory = fn(...$arg) => $class->newInstance(...$arg);
		return new DtoClassTypeResolver($factory, $methodParametersResolver);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function createFactoryMethodClassTypeResolver(
		\ReflectionClass $class,
		string $factory_method_name,
	) : TypeResolver {

		$factoryMethod = $class->getMethod($factory_method_name);

		if (!$factoryMethod->isPublic()) {
			throw new \InvalidArgumentException("Factory method `$factory_method_name` must be public");
		}

		if(!$factoryMethod->isStatic()) {
			throw new \InvalidArgumentException("Factory method `$factory_method_name` must be static");
		}

		$parameters = $factoryMethod->getParameters();
		if (\count($parameters) === 1) {
			$param = $parameters[0];
			$param_resolver = $this->createParameterResolver($param);
			$factory = fn($arg) => $factoryMethod->invoke(null, $arg);
			return new EnvelopeClassTypeResolver($factory, $param_resolver);
		}

		$methodParametersResolver = $this->createMethodResolver($factoryMethod);
		$factory = fn(...$arg) => $factoryMethod->invoke(null, ...$arg);
		return new DtoClassTypeResolver($factory, $methodParametersResolver);
	}

}
