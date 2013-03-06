<?php

namespace pq\Query;

class Expr
{
	/**
	 * @var string
	 */
	protected $expression;
	
	/**
	 * @var \pq\Query\Expr
	 */
	protected $next;

	/**
	 * @param string $e the expression or a format string followed by arguments
	 * @param string ...
	 */
	function __construct($e, $arg = null) {
		if (func_num_args() > 1) {
			$e = call_user_func_array("sprintf", func_get_args());
		}
		$this->expression = trim($e);
	}

	/**
	 * Get the string expression
	 * @return string
	 */
	function __toString() {
		$string = $this->expression;
		if ($this->next) {
			$string .= " " . $this->next;
		}
		return (string) $string;
	}
	
	/**
	 * Check for NULL
	 * @return bool
	 */
	function isNull() {
		return !strcasecmp($this->expression, "null");
	}
	
	/**
	 * Append an expresssion
	 * @param \pq\Query\Expr $next
	 * @return \pq\Query\Expr $this
	 * @throws \UnexpectedValueException if any expr is NULL
	 */
	function add(Expr $next) {
		if ($this->isNull() || $next->isNull()) {
			throw new \UnexpectedValueException("Cannot add anything to NULL");
		}
		for ($that = $this; $that->next; $that = $that->next);
		$that->next = $next;
		return $this;
	}
}
