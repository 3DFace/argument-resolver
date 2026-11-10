<?php

namespace dface\ArgumentResolver;

readonly class EnvelopeClassTypeResolver implements TypeResolver
{

	public function __construct(
		private \Closure $factory,
		private TypeResolver $param_resolver,
	) {
	}

	public function getName() : string
	{
		return $this->param_resolver->getName();
	}

	public function strictResolve(mixed $value) : array
	{
		[$resolvable, $result] = $this->param_resolver->strictResolve($value);
		if (!$resolvable) {
			return [false, $result];
		}
		try {
			$obj = ($this->factory)($result);
		} catch (\Throwable $e) {
			return [false, $e->getMessage()];
		}
		return [true, $obj];
	}

	public function weakResolve(mixed $value) : array
	{
		[$resolvable, $result] = $this->param_resolver->weakResolve($value);
		if (!$resolvable) {
			return [false, $result];
		}
		try {
			$obj = ($this->factory)($result);
		} catch (\Throwable $e) {
			return [false, $e->getMessage()];
		}
		return [true, $obj];
	}

}
