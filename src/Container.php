<?php

namespace Kaiser;

use Pimple\Container as PimpleContainer;

interface ContainerInterface {
	public function get($id);
	public function has($id);
}
class Container extends PimpleContainer implements ContainerInterface {
	function __construct(array $values = []) {
		parent::__construct ( $values );
		
		$this->registerDefaultServices ();
	}
	private function registerDefaultServices() {
		if (! isset ( $this ['request'] )) {
			$this ['request'] = function ($c) {
				return new Request ();
			};
		}
		if (! isset ( $this ['router'] )) {
			$this ['router'] = function ($c) {
				return new Router ();
			};
		}
		// KLogger: Simple Logging for PHP
		// https://github.com/katzgrau/KLogger
		if (! isset ( $this ['logger'] )) {
			$this ['logger'] = function ($c) {
				$logger = new Manager\LogManager (__DIR__.'/../log');
				return $logger;
			};
		}
		if (! isset ( $this ['session'] )) {
			$this ['session'] = function ($c) {
				$session = new Session\FileSession (__DIR__.'/../tmp');
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