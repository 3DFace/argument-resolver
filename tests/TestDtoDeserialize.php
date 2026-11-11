<?php

namespace dface\ArgumentResolver;

readonly class TestDtoDeserialize extends TestDto
{

	public static function deserialize(array|object $data) : self
	{
		if (!\array_key_exists('prop1', $data)) throw new \InvalidArgumentException('Missing prop1');
		if (!\array_key_exists('prop2', $data)) throw new \InvalidArgumentException('Missing prop2');
		if (!\array_key_exists('prop3', $data)) throw new \InvalidArgumentException('Missing prop3');
		if (!\array_key_exists('prop4', $data)) throw new \InvalidArgumentException('Missing prop4');
		return new self($data['prop1'], $data['prop2'], $data['prop3'], new TestEmail($data['prop4']));
	}

}
