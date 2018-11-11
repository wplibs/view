<?php

use WPLibs\View\Engine_Resolver;

class EngineResolverTest extends \WP_UnitTestCase {
	public function testResolversMayBeResolved() {
		$resolver = new Engine_Resolver;

		$resolver->register( 'foo', function () {
			return new stdClass;
		} );

		$result = $resolver->resolve( 'foo' );

		$this->assertEquals( spl_object_hash( $result ), spl_object_hash( $resolver->resolve( 'foo' ) ) );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testResolverThrowsExceptionOnUnknownEngine() {
		$resolver = new Engine_Resolver;
		$resolver->resolve( 'foo' );
	}
}
