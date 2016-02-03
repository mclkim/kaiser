<?php
if (! function_exists ( 'app' )) {
	/**
	 * Get the available container instance.
	 *
	 * @param string $make        	
	 * @param array $parameters        	
	 * @return mixed|\Illuminate\Foundation\Application
	 */
	function app($make = null, $parameters = []) {
		if (is_null ( $make ))
			return Container::getInstance ();
		
		return Container::getInstance ()->make ( $make, $parameters );
	}
}
if (! function_exists ( 'logger' )) {
	/**
	 * Log a debug message to the logs.
	 *
	 * @param string $message        	
	 * @param array $context        	
	 * @return null|\Illuminate\Contracts\Logging\Log
	 */
	function logger($message = null, array $context = array()) {
		if (is_null ( $message ))
			return app ( 'log' );
		
		return app ( 'log' )->debug ( $message, $context );
	}
}