<?php

namespace dface\ArgumentResolver;

class TypeResolverHelper
{
	public static function makeBadTypeMessage(string $expected_type, mixed $value) : string
	{
		$given_type = \gettype($value);
		$given_type_pretty = match ($given_type) {
			'NULL' => 'null',
			'boolean' => 'bool',
			'integer' => 'int',
			'double' => 'float',
			default => $given_type,
		};
		return $expected_type.' expected, '.$given_type_pretty.' given';
	}

	public static function cleanUpTypeErrorMessage(string $msg) : string
	{
		if ($called_in = \strpos($msg, ', called in')) {
			$msg = \substr($msg, 0, $called_in);
		}
		return \preg_replace("|[\w\\\\]+::|", '', $msg);
	}
}
