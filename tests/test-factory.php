<?php

use WPLibs\View\Factory;

class FactoryTest extends \WP_UnitTestCase {
	public function testFactoryDefault() {
		$view = Factory::create( [ 'paths' => __DIR__ . '/fixtures' ] );

		$this->assertEquals( [ 'php' => 'php' ], $view->get_extensions() );
		$this->assertEquals( 'Hello World' . "\n", $view->make( 'basic' )->render() );
	}

	public function testFactoryTwig() {
		$view = Factory::create( [ 'paths' => __DIR__ . '/fixtures', 'engines' => [ 'php', 'twig' ] ] );

		$this->assertEquals( 'Hello World' . "\n", $view->make( 'basic' )->render() );
		$this->assertEquals( 'Hello World' . "\n", $view->make( 'twig' )->render() );
	}
}
