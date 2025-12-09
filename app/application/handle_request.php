<?php

	$path = get_clean_user_input('path','[a-zA-Z0-9\/\.\-\_]');
	$action = get_clean_user_input('action');

	//$path = $action ?? $path;

//	var_dump([
//			'path'=>$path ?? 'No path set',
//			'url'=>$_SERVER['REQUEST_URI'],
//		]);

	if ( empty($path) && !isset($DEFAULT_ROUTE)) {
		throw new Exception(get_text('Required parameter missing: %s',['path']));
	}
	$route = empty($path) ? $DEFAULT_ROUTE : $path;

	if ($action) {
		$route = $route.'/'.$action;
	}

	$routeClassName = str_replace('/','\\',$route);
	$className = implode('\\',[$ROUTES_NAMESPACE, $routeClassName]);
	$classNameDefaulted = implode('\\',[$ROUTES_NAMESPACE, $DEFAULT_ROUTE_NAMESPACE, $routeClassName]);

	if ( class_exists($className) ) {
	} else if ( class_exists($classNameDefaulted) ) {
		$className = $classNameDefaulted;
	} else {
		throw new Exception(get_text('Unknown route: %s (%s)',[$route, $className]));
	}

	$routeObject = null;
	try {
		$routeObject = new $className();
	} catch (\Throwable $e) {
		throw new Exception( get_text('Could not load route %s',[$route]), previous:$e);
	}

	$output = $routeObject->startRoute($_INPUTS_CLEANED);
	if ( $output instanceof \Rendering\Renderable ) {
		$output->output();
	} else {
		echo $output;
	}

?>
