<?php

namespace WPLibs\View;

class Engine_Resolver {
	/**
	 * The array of engine resolvers.
	 *
	 * @var array
	 */
	protected $resolvers = [];

	/**
	 * The resolved engine instances.
	 *
	 * @var array
	 */
	protected $resolved = [];

	/**
	 * Register a new engine resolver.
	 *
	 * The engine string typically corresponds to a file extension.
	 *
	 * @param  string   $engine
	 * @param  callable $resolver
	 * @return void
	 */
	public function register( $engine, callable $resolver ) {
		unset( $this->resolved[ $engine ] );

		$this->resolvers[ $engine ] = $resolver;
	}

	/**
	 * Resolve an engine instance by name.
	 *
	 * @param  string $engine
	 * @return \WPLibs\View\Engine
	 * @throws \InvalidArgumentException
	 */
	public function resolve( $engine ) {
		if ( isset( $this->resolved[ $engine ] ) ) {
			return $this->resolved[ $engine ];
		}

		if ( isset( $this->resolvers[ $engine ] ) ) {
			return $this->resolved[ $engine ] = call_user_func( $this->resolvers[ $engine ] );
		}

		throw new \InvalidArgumentException( "Engine $engine not found." );
	}
}
