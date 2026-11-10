<?php

namespace dface\ArgumentResolver;

readonly class TestDtoNest
{

	public function __construct(
		public TestDto $field1,
		public TestEnvelope $field2,
		public bool $field3,
	) {
	}

}
