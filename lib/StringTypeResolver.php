<?php

namespace dface\ArgumentResolver;

class StringTypeResolver implements TypeResolver
{

	public function getName() : string
	{
		return 'string';
	}

	public function strictResolve(mixed $value) : array
	{
		return \is_string($value) ? [true, $value] : [false, TypeResolverHelper::makeBadTypeMessage('string', $value)];
	}

	public function weakResolve(mixed $value) : array
	{
		return \is_scalar($value) ? [true, (string)$value] : [false, TypeResolverHelper::makeBadTypeMessage('string', $value)];
	}

}
