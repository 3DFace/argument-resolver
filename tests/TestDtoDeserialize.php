<?php

namespace dface\ArgumentResolver;

readonly class TestDtoDeserialize extends TestDto
{

	public static function deserialize(array|object $data) : self
	{
		if (!isset($data['prop1'])) throw new \InvalidArgumentException('Missing prop1');
		if (!isset($data['prop2'])) throw new \InvalidArgumentException('Missing prop2');
		if (!isset($data['prop3'])) throw new \InvalidArgumentException('Missing prop3');
		if (!isset($data['prop4'])) throw new \InvalidArgumentException('Missing prop4');
		return new self($data['prop1'], $data['prop2'], $data['prop3'], new TestEmail($data['prop4']));
	}

}
