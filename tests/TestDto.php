<?php

namespace dface\ArgumentResolver;

readonly class TestDto
{

	public function __construct(
		public string $field1,
		public int $field2,
		public bool $field3,
		public TestEmail $field4,
	) {
		if($this->field2 > 100){
			throw new \InvalidArgumentException("'field2' must be less than 100");
		}
	}

}
