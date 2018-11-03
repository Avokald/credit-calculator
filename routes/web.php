<?php 

if (($_SERVER['REQUEST_URI'] === '/')
	|| ($_SERVER['REQUEST_URI'] === '/index')
	|| ($_SERVER['REQUEST_URI'] === '/index.php')) {
	
	if ($_SERVER['REQUEST_METHOD'] === 'GET') {
		include '../app/Controllers/IndexController.php';
		index();
	}
	else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		include '../app/Controllers/IndexController.php';
		store();
	}
	else {
		include '../resources/views/404.php';
	}
}
else if (($_SERVER['REQUEST_URI'] === '/result')
	|| ($_SERVER['REQUEST_URI'] === '/result.php')
	|| (preg_match('/^\/result(\.php)?\?h=[a-zA-Z0-9]*/', $_SERVER['REQUEST_URI']))) {
	
	if ($_SERVER['REQUEST_METHOD'] === 'GET') {
		include '../app/Controllers/ResultController.php';
		index();
	}
	else {
		include '../resources/views/404.php';
	}
}


else {
	include '../resources/views/404.php';
}