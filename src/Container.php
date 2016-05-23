<?php

namespace Kaiser;

use Pimple\Container as PimpleContainer;

interface ContainerInterface {
	public function get($id);
	public function has($id);
}
class Container extends PimpleContainer implements ContainerInterface {
	/**
	 * The current globally available container (if any).
	 *
	 * @var static
	 */
	function __construct(array $values = []) {
		parent::__construct ( $values );
		// var_dump ( $this );
		// exit ();
		$this->registerDefaultServices ();
	}
	private function registerDefaultServices() {
		if (! isset ( $this ['config'] )) {
			$this ['config'] = function ($c) {
				return new Config ();
			};
		}		
		if (! isset ( $this ['request'] )) {
			$this ['request'] = function ($c) {
				return new Request ();
			};
		}
		if (! isset ( $this ['response'] )) {
			$this ['response'] = function ($c) {
				return new Response ();
			};
		}		
		if (! isset ( $this ['router'] )) {
			$this ['router'] = function ($c) {
				return new Router ();
			};
		}
		/**
		 * TODO::
		 *
		 * KLogger: Simple Logging for PHP
		 * https://github.com/katzgrau/KLogger
		 */
		if (! isset ( $this ['logger'] )) {
			$this ['logger'] = function ($c) {
				$logger = new Manager\LogManager ( __DIR__ . '/../log' );
				return $logger;
			};
		}
		if (! isset ( $this ['session'] )) {
			$this ['session'] = function ($c) {
				$session = new Session\FileSession ( __DIR__ . '/../tmp' );
				$session->start_session ();
				return $session;
			};
		}
	}
	function get($id) {
		if (! $this->offsetExists ( $id )) {
			throw new \RuntimeException ( sprintf ( 'Identifiler "%s" is not defined.', $id ) );
		}
		return $this->offsetGet ( $id );
	}
	function has($id) {
		return $this->offsetExists ( $id );
	}
}