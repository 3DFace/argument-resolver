<?php

namespace dface\ArgumentResolver;

readonly class TestEmail
{

	public string $value;

	public function __construct(string $value)
	{
		$email = \trim($value);
		if (!\preg_match('/^[^@]+@[^@]+$/', $email)) {
			throw new \InvalidArgumentException("Invalid email address");
		}
		$this->value = $email;
	}
}
