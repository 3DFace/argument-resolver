<?php

namespace dface\ArgumentResolver;

readonly class MethodParametersResolver implements TypeResolver
{

	public function __construct(
		private array $parameters_and_resolvers,
		private bool $has_variadic,
	) {
	}

	public function getName() : string
	{
		$arr = [];
		foreach ($this->parameters_and_resolvers as [$param, $type_resolver]) {
			/** @var \ReflectionParameter $param */
			/** @var TypeResolver $type_resolver */
			$arr[] = $param->getName().':'.$type_resolver->getName();
		}
		return '['.\implode(', ', $arr).']';
	}

	public function strictResolve(mixed $value) : array
	{
		return $this->resolve($value, true);
	}

	public function weakResolve(mixed $value) : array
	{
		return $this->resolve($value, false);
	}

	private function resolve(mixed $raw_data, bool $strict) : array
	{
		if (\is_array($raw_data)) {
			$is_list = \array_is_list($raw_data);
		} elseif (\is_object($raw_data)) {
			$is_list = false;
			$raw_data = (array)$raw_data; // associative array
		} else {
			return [false, $this->getName().' expected'];
		}

		if (!$is_list && $this->has_variadic) {
			return [false, "cant use variadic parameters with associative arguments"];
		}

		$parameters_errors = [];
		$result = [];
		foreach ($this->parameters_and_resolvers as $param_index => [$param, $type_resolver]) {
			/** @var \ReflectionParameter $param */
			/** @var TypeResolver $type_resolver */

			$parameter_name = $param->getName();

			$key = $is_list ? $param_index : $parameter_name;

			if ($param->isVariadic()) {
				$raw_data_tail = \array_slice($raw_data, $param_index);
				foreach ($raw_data_tail as $tail_index => $raw_value) {
					$this->resolveValue(
						$type_resolver,
						$strict,
						$raw_value,
						$result,
						$parameters_errors,
						$param_index + $tail_index,
						$parameter_name.':'.$tail_index,
					);
				}
				// variadic parameter is always last
				break;
			}

			if (!\array_key_exists($key, $raw_data)) {
				if ($param->isDefaultValueAvailable()) {
					$result[$key] = $param->getDefaultValue();
				} else {
					$result[$key] = null;
					$parameters_errors[$parameter_name] = "is mandatory";
				}
			} else {
				$raw_value = $raw_data[$key];
				$this->resolveValue(
					$type_resolver,
					$strict,
					$raw_value,
					$result,
					$parameters_errors,
					$key,
					$parameter_name,
				);
			}
		}

		if ($parameters_errors) {
			$error_list = \array_map(
				fn($k, $v) => "'$k': $v",
				\array_keys($parameters_errors),
				\array_values($parameters_errors),
			);
			$error = \count($error_list) === 1 ? $error_list[0] : ('['.\implode(', ', $error_list).']');
			return [false, $error];
		}

		return [true, $result];
	}

	private function resolveValue(
		TypeResolver $type_resolver,
		bool $strict,
		mixed $raw_value,
		array &$result,
		array &$parameters_errors,
		int|string $parameter_key,
		string $parameter_name,
	) : void {

		[$success, $resolve_result] = $strict
			? $type_resolver->strictResolve($raw_value)
			: $type_resolver->weakResolve($raw_value);

		if ($success) {
			$result[$parameter_key] = $resolve_result;
		} else {
			$result[$parameter_key] = null;
			$parameters_errors[$parameter_name] = $resolve_result;
		}
	}

}
