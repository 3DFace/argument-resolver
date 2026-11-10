<?php

namespace dface\ArgumentResolver;

class ArrayTypeResolver implements TypeResolver
{

	public function getName() : string
	{
		return 'array';
	}

	public function strictResolve(mixed $value) : array
	{
		return \is_array($value) ? [true, $value] : [false, 'array expected'];
	}

	public function weakResolve(mixed $value) : array
	{
		return \is_array($value) ? [true, $value] : [false, 'array expected'];
	}

}
