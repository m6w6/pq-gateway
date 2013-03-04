<?php

namespace pq\Query;

class Expr
{
	/**
	 * @var string
	 */
	protected $expression;

	/**
	 * @param string $e the expression or a format string followed by arguments
	 * @param string ...
	 */
	function __construct($e) {
		if (func_num_args() > 1) {
			$this->expression = call_user_func_array("sprintf", func_get_args());
		} else {
			$this->expression = $e;
		}
	}

	/**
	 * Get the string expression
	 * @return string
	 */
	function __toString() {
		return (string) $this->expression;
	}
}
