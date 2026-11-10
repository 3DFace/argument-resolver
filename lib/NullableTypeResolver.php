<?php

namespace dface\ArgumentResolver;

readonly class NullableTypeResolver implements TypeResolver
{

	public function __construct(private TypeResolver $inner_resolver)
	{
	}

	public function getName() : string
	{
		return '?'.$this->inner_resolver->getName();
	}

	public function strictResolve(mixed $value) : array
	{
		return $value === null ? [true, null] : $this->inner_resolver->strictResolve($value);
	}

	public function weakResolve(mixed $value) : array
	{
		return $value === null ? [true, null] : $this->inner_resolver->weakResolve($value);
	}

}
