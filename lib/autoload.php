<?php

spl_autoload_register(function($c) {
	if (substr($c, 0, 11) === "pq\\Gateway\\" || substr($c, 0, 9) === "pq\\Query\\" || substr($c, 0, 10) === "pq\\Mapper\\") {
		return include __DIR__ . "/" . strtr($c, "\\", "/") . ".php";
	}
});
