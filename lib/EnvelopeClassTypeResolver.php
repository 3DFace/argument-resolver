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
		return $this->convertResolveResult($this->param_resolver->strictResolve($value));
	}

	public function weakResolve(mixed $value) : array
	{
		return $this->convertResolveResult($this->param_resolver->weakResolve($value));
	}

	private function convertResolveResult(array $resolve_result) : array
	{
		[$resolvable, $result] = $resolve_result;
		if (!$resolvable) {
			return [false, $result];
		}
		try {
			$obj = ($this->factory)($result);
		} catch (\TypeError $e) {
			$msg = $e->getMessage();
			return [false, TypeResolverHelper::cleanUpTypeErrorMessage($msg)];
		} catch (\Throwable $e) {
			return [false, $e->getMessage()];
		}
		return [true, $obj];
	}

}
