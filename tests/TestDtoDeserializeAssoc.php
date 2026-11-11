<?php

namespace dface\ArgumentResolver;

readonly class TestDtoDeserializeAssoc extends TestDto
{

	public static function deserialize(array|object $data) : self
	{
		if (!\array_key_exists('prop1', $data)) throw new \InvalidArgumentException('Missing prop1');
		if (!\array_key_exists('prop2', $data)) throw new \InvalidArgumentException('Missing prop2');
		if (!\array_key_exists('prop3', $data)) throw new \InvalidArgumentException('Missing prop3');
		if (!\array_key_exists('prop4', $data)) throw new \InvalidArgumentException('Missing prop4');
		return new self(
			field1: $data['prop1'],
			field2: $data['prop2'],
			field3: $data['prop3'],
			field4: new TestEmail($data['prop4']),
		);
	}

}
