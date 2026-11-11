<?php

namespace dface\ArgumentResolver;

class TestApiService
{

	public function simpleParameters(int $param1, string $param2, bool $param3, array $param4) : void
	{
	}

	public function parametersWithDefaults(int $param1, string $param2 = 'default', $any = "1") : void
	{
	}

	public function variadicParameters(float $param1, int ...$variadic) : void
	{
	}

	public function envelopeParameter(TestEnvelope $param1) : void
	{
	}

	public function dtoParameter(TestDto $param1) : void
	{
	}

	public function dtoNestParameter(TestDtoNest $param1, mixed $param2) : void
	{
	}

	public function dtoParameterDeserialize(TestDtoDeserialize $param1) : void
	{
	}

	public function dtoParameterDeserializeAssoc(TestDtoDeserializeAssoc $param1) : void
	{
	}

	public function unionParameter(TestEnvelope|int|null $param1) : void
	{
	}

	public function unionParameterAllStrings(TestEmail|TestPhone|string $param1) : void
	{

	}

}
