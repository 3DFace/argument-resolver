<?php

namespace dface\ArgumentResolver;

readonly class NullTypeResolver implements TypeResolver
{

	public function getName() : string
	{
		return 'null';
	}

	public function strictResolve(mixed $value) : array
	{
		return $value === null ? [true, null] : [false, 'null expected'];
	}

	public function weakResolve(mixed $value) : array
	{
		return $value === null ? [true, null] : [false, 'null expected'];
	}

}
