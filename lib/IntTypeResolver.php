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
		return \is_int($value) ? [true, $value] : [false, 'int expected'];
	}

	public function weakResolve(mixed $value) : array
	{
		return \is_numeric($value) && (float)(int)$value === (float)$value ? [true, (int)$value] : [false, 'int expected'];
	}

}
