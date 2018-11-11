<?php

namespace WPLibs\View\Engines;

use Twig_Environment;
use WPLibs\View\Engine;

class Twig_Engine implements Engine {
	/**
	 * The Twig Environment instance.
	 *
	 * @var \Twig_Environment
	 */
	protected $environment;

	/**
	 * Constructor.
	 *
	 * @param \Twig_Environment $environment
	 */
	public function __construct( Twig_Environment $environment ) {
		$this->environment = $environment;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get( $name, array $data = [] ) {
		return ltrim( $this->environment->render( $name, $data ) );
	}
}
