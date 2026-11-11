<?php

namespace dface\ArgumentResolver;

class IntTypeResolver implements TypeResolver
{

	public function getName() : string
	{
		return 'int';
	}

	public function strictResolve(mixed $value) : array
	{
		return \is_int($value) ? [true, $value] : [false, TypeResolverHelper::makeBadTypeMessage('int', $value)];
	}

	public function weakResolve(mixed $value) : array
	{
		if (\is_int($value)) {
			return [true, $value];
		}
		if (\is_numeric($value)) {
			$int_val = (int)$value;
			$float_val = (float)$value;
			if ((float)$int_val === $float_val) {
				return [true, $int_val];
			}
		}
		return [false, TypeResolverHelper::makeBadTypeMessage('int', $value)];
	}

}
