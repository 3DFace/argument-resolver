<?php

namespace dface\ArgumentResolver;


readonly class TestPhone
{

	public string $value;

	public function __construct(string $value)
	{
		if (\preg_match('|^\d{12}$|', $value) !== 1) {
			throw new \InvalidArgumentException('Phone number must be a 12-digits length string');
		}
		$this->value = $value;
	}
}
