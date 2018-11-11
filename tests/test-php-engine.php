<?php

use WPLibs\View\Engines\Php_Engine;

class PhpEngineTest extends \WP_UnitTestCase {
	public function testViewsMayBeProperlyRendered() {
		$engine = new Php_Engine;
		$this->assertEquals( 'Hello World' . "\n", $engine->get( __DIR__ . '/fixtures/basic.php' ) );
	}
}
