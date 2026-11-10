<?php

namespace dface\ArgumentResolver;

readonly class UnionTypeResolver implements TypeResolver
{

	/**
	 * @param TypeResolver[] $subtypes_resolvers
	 */
	public function __construct(private array $subtypes_resolvers)
	{
	}

	public function getName() : string
	{
		$names = [];
		foreach ($this->subtypes_resolvers as $subtype_resolver) {
			$names[$subtype_resolver->getName()] = 1;
		}
		return \implode('|', \array_keys($names));
	}

	public function strictResolve(mixed $value) : array
	{
		foreach ($this->subtypes_resolvers as $subtype_resolver) {
			[$success, $result] = $subtype_resolver->strictResolve($value);
			if ($success) {
				return [$success, $result];
			}
		}
		return [false, null];
	}

	public function weakResolve(mixed $value) : array
	{
		// choose strict match first
		[$success, $result] = $this->strictResolve($value);
		if ($success) {
			return [$success, $result];
		}

		// otherwise try to match weak any subtype
		foreach ($this->subtypes_resolvers as $subtype_resolver) {
			[$success, $result] = $subtype_resolver->weakResolve($value);
			if ($success) {
				return [$success, $result];
			}
		}
		return [false, $this->getName().' expected'];
	}

}
