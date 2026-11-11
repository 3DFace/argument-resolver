<?php

namespace dface\ArgumentResolver;

class FloatTypeResolver implements TypeResolver
{

	public function getName() : string
	{
		return 'float';
	}

	public function strictResolve(mixed $value) : array
	{
		return (\is_float($value) || is_int($value)) ? [true, $value] : [false, 'float expected'];
	}

	public function weakResolve(mixed $value) : array
	{
		if (\is_float($value) || \is_int($value)) {
			return [true, $value];
		}
		return \is_numeric($value) ? [true, (float)$value] : [false, 'float expected'];
	}

}
