<?php

namespace dface\ArgumentResolver;

class ObjectTypeResolver implements TypeResolver
{

	public function getName() : string
	{
		return 'object';
	}

	public function strictResolve(mixed $value) : array
	{
		return \is_object($value) ? [true, $value] : [false, 'object expected'];
	}

	public function weakResolve(mixed $value) : array
	{
		return (\is_object($value) || \is_array($value) && !\array_is_list($value))
			? [true, (object)$value]
			: [false, 'object|assoc expected'];
	}

}
