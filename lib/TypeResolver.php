<?php

namespace dface\ArgumentResolver;

interface TypeResolver
{

	public function getName() : string;

	public function strictResolve(mixed $value) : array;

	public function weakResolve(mixed $value) : array;

}
