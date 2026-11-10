<?php

namespace dface\ArgumentResolver;

class MixedTypeResolver implements TypeResolver
{

	public function getName() : string
	{
		return 'mixed';
	}

	public function strictResolve(mixed $value) : array
	{
		return [true, $value];
	}

	public function weakResolve(mixed $value) : array
	{
		return [true, $value];
	}

}
