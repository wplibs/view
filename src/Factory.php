<?php

namespace WPLibs\View;

use Twig_Environment;

class Factory {
	/**
	 * Store the finder.
	 *
	 * @var \WPLibs\View\File_Finder
	 */
	protected $finder;

	/**
	 * Store the twig instance.
	 *
	 * @var \Twig_Environment
	 */
	protected $twig;

	/**
	 * Create new view factory by array config.
	 *
	 * @param array $config
	 * @return \WPLibs\View\View_Factory
	 */
	public static function create( array $config ) {
		$config = wp_parse_args( $config, [
			'paths'   => [],
			'engines' => [ 'php' ],
			'twig'    => [],
		] );

		$factory = new static( $config['paths'] );

		$view = $factory->create_view(
			$factory->create_engine_resolver( 'php' )
		);

		if ( in_array( 'twig', $config['engines'] ) ) {
			$factory->set_twig(
				$factory->create_twig_environment( $config['twig'] )
			);

			$view->add_extension( 'twig', 'twig' );

			$factory->register_twig_engine( $view->get_engine_resolver() );
		}

		return $view;
	}

	/**
	 * Constructor.
	 *
	 * @param array|string $paths
	 */
	public function __construct( $paths ) {
		$this->finder = new File_Finder( ! is_array( $paths ) ? [ $paths ] : $paths );
	}

	/**
	 * Register the view factory.
	 *
	 * @param \WPLibs\View\Engine_Resolver|null $resolver
	 * @return \WPLibs\View\View_Factory
	 */
	public function create_view( Engine_Resolver $resolver = null ) {
		$resolver = $resolver ?: $this->create_engine_resolver( 'php' );

		return new View_Factory( $resolver, $this->finder );
	}

	/**
	 * Return the Twig Environment.
	 *
	 * @param array $options
	 * @return \Twig_Environment
	 */
	public function create_twig_environment( $options = [] ) {
		$loader = new Twig\Loader( $this->finder );

		$options = wp_parse_args( $options, [
			'debug'       => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'cache'       => false,
			'auto_reload' => true,
		] );

		$twig = new Twig_Environment( $loader, $options );

		$twig->addExtension( new Twig\Extensions\WP_Twig_Extension );

		return $twig;
	}

	/**
	 * Register the Engine_Resolver instance to the plugin.
	 *
	 * @param array|string $engines
	 * @return \WPLibs\View\Engine_Resolver
	 */
	public function create_engine_resolver( $engines ) {
		$resolver = new Engine_Resolver;

		foreach ( is_array( $engines ) ? $engines : func_get_args() as $engine ) {
			$this->{"register_{$engine}_engine"}( $resolver );
		}

		return $resolver;
	}

	/**
	 * Register the PHP engine to the Engine_Resolver.
	 *
	 * @param \WPLibs\View\Engine_Resolver $resolver
	 */
	public function register_php_engine( Engine_Resolver $resolver ) {
		$resolver->register( 'php', function () {
			return new Engines\Php_Engine;
		} );
	}

	/**
	 * Register the Twig engine to the Engine_Resolver.a
	 *
	 * @param \WPLibs\View\Engine_Resolver $resolver
	 */
	public function register_twig_engine( Engine_Resolver $resolver ) {
		$resolver->register( 'twig', function () {
			return new Engines\Twig_Engine( $this->get_twig() );
		} );
	}

	/**
	 * Sets the Twig environment.
	 *
	 * @param \Twig_Environment $twig
	 * @return $this
	 */
	public function set_twig( Twig_Environment $twig ) {
		$this->twig = $twig;

		return $this;
	}

	/**
	 * Return the Twig environment.
	 *
	 * @return \Twig_Environment
	 */
	public function get_twig() {
		if ( ! $this->twig ) {
			$this->twig = $this->create_twig_environment();
		}

		return $this->twig;
	}
}
