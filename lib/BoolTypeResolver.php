<?php

namespace dface\ArgumentResolver;

class BoolTypeResolver implements TypeResolver
{

	public function getName() : string
	{
		return 'bool';
	}

	public function strictResolve(mixed $value) : array
	{
		return \is_bool($value) ? [true, $value] : [false, 'bool expected'];
	}

	public function weakResolve(mixed $value) : array
	{
		$bool = \filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
		return $bool === null ? [false, 'bool expected'] : [true, $bool];
	}

}
