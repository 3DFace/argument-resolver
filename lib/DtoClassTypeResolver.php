<?php

namespace dface\ArgumentResolver;

readonly class DtoClassTypeResolver implements TypeResolver
{

	public function __construct(
		private \Closure $factory,
		private MethodParametersResolver $methodParametersResolver,
	) {
	}

	public function getName() : string
	{
		return $this->methodParametersResolver->getName();
	}

	public function strictResolve(mixed $value) : array
	{
		[$resolvable, $result] = $this->methodParametersResolver->strictResolve($value);
		if (!$resolvable) {
			return [false, $result];
		}
		try {
			$obj = ($this->factory)(...$result);
		} catch (\Throwable $e) {
			return [false, $e->getMessage()];
		}
		return [true, $obj];
	}

	public function weakResolve(mixed $value) : array
	{
		[$resolvable, $result] = $this->methodParametersResolver->weakResolve($value);
		if (!$resolvable) {
			return [false, $result];
		}
		try {
			$obj = ($this->factory)(...$result);
		} catch (\Throwable $e) {
			return [false, $e->getMessage()];
		}
		return [true, $obj];
	}

}
