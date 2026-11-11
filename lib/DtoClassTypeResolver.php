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
		return $this->convertResolveResult($this->methodParametersResolver->strictResolve($value));
	}

	public function weakResolve(mixed $value) : array
	{
		return $this->convertResolveResult($this->methodParametersResolver->weakResolve($value));
	}

	private function convertResolveResult(array $resolve_result) : array
	{
		[$resolvable, $result] = $resolve_result;
		if (!$resolvable) {
			return [false, $result];
		}
		try {
			$obj = ($this->factory)(...$result);
		} catch (\TypeError $e) {
			$msg = $e->getMessage();
			return [false, TypeResolverHelper::cleanUpTypeErrorMessage($msg)];
		} catch (\Throwable $e) {
			return [false, $e->getMessage()];
		}
		return [true, $obj];
	}

}
