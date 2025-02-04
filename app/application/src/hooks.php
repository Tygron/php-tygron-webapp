<?php

	$hooks = [];
	foreach ( ($HOOKS??[]) as $key=>$value ) {
		$className = '\\Hooks\\'.$value;
		if ( empty($className) ) {
			continue;
		}
		if ( !class_exists($className) )  {
			throw new \Exception(get_text('Unknown hook: %s',[$value]));
		}
		try {
			$hook = new $className();
			$hooks[$value] = $hook;
		} catch ( \Throwable $e ) {
			throw new \Exception( get_text('Could not load hook %s',[$value]), previous:$e);
		}
	}

	foreach ( $hooks as $index => $hook ) {
		$hook->run();
	}
?>
