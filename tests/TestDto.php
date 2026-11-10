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
	}

}
