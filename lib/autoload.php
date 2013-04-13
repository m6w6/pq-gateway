<?php

spl_autoload_register(function($c) {
	if (substr($c, 0, 10) === "pq\\Gateway" || substr($c, 0, 8) === "pq\\Query") {
		return include __DIR__ . "/" . strtr($c, "\\", "/") . ".php";
	}
});
