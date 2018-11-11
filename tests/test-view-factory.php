<?php

use Mockery as m;
use WPLibs\View\Engine;
use WPLibs\View\Finder;
use WPLibs\View\Engine_Resolver;
use WPLibs\View\View_Factory;

class ViewFactoryTest extends \WP_UnitTestCase {
	public function tearDown() {
		parent::tearDown();
		m::close();
	}

	public function testMakeCreatesNewViewInstanceWithProperPathAndEngine() {
		$factory = $this->getFactory();

		$factory->get_finder()->shouldReceive( 'find' )->once()->with( 'view' )->andReturn( 'path.php' );
		$factory->get_engine_resolver()->shouldReceive( 'resolve' )->once()->with( 'php' )->andReturn( $engine = m::mock( Engine::class ) );
		$factory->get_finder()->shouldReceive( 'add_extension' )->once()->with( 'php' );

		$factory->add_extension( 'php', 'php' );
		$view = $factory->make( 'view', [ 'foo' => 'bar' ], [ 'baz' => 'boom' ] );

		$this->assertSame( $engine, $view->get_engine() );
	}

	public function testExistsPassesAndFailsViews() {
		$factory = $this->getFactory();

		$factory->get_finder()->shouldReceive( 'find' )->once()->with( 'foo' )->andThrow( InvalidArgumentException::class );
		$factory->get_finder()->shouldReceive( 'find' )->once()->with( 'bar' )->andReturn( 'path.php' );

		$this->assertFalse( $factory->exists( 'foo' ) );
		$this->assertTrue( $factory->exists( 'bar' ) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testThrowsInvalidArgumentExceptionIfNotFound() {
		$factory = $this->getFactory();

		$factory->get_finder()->shouldReceive( 'find' )->once()->with( 'view' )->andThrow( InvalidArgumentException::class );
		$factory->get_engine_resolver()->shouldReceive( 'resolve' )->with( 'php' )->andReturn( $engine = m::mock( Engine::class ) );
		$factory->get_finder()->shouldReceive( 'add_extension' )->with( 'php' );

		$factory->add_extension( 'php', 'php' );
		$factory->make('view', [ 'foo' => 'bar' ]);
	}

	public function testRenderEachCreatesViewForEachItemInArray() {
		$factory = m::mock( View_Factory::class . '[make]', $this->getFactoryArgs() );

		$factory->shouldReceive( 'make' )->once()->with( 'foo', [ 'key' => 'bar', 'value' => 'baz' ] )->andReturn( $mockView1 = m::mock( stdClass::class ) );
		$factory->shouldReceive( 'make' )->once()->with( 'foo', [ 'key' => 'breeze', 'value' => 'boom' ] )->andReturn( $mockView2 = m::mock( stdClass::class ) );
		$mockView1->shouldReceive( 'render' )->once()->andReturn( 'dayle' );
		$mockView2->shouldReceive( 'render' )->once()->andReturn( 'rees' );

		$result = $factory->render_each( 'foo', [ 'bar' => 'baz', 'breeze' => 'boom' ], 'value' );

		$this->assertEquals( 'daylerees', $result );
	}

	public function testEmptyViewsCanBeReturnedFromRenderEach() {
		$factory = m::mock( View_Factory::class . '[make]', $this->getFactoryArgs() );

		$factory->shouldReceive( 'make' )->once()->with( 'foo' )->andReturn( $mockView = m::mock( stdClass::class ) );
		$mockView->shouldReceive( 'render' )->once()->andReturn( 'empty' );

		$this->assertEquals( 'empty', $factory->render_each( 'view', [], 'iterator', 'foo' ) );
	}

	public function testEnvironmentAddsExtensionWithCustomResolver() {
		$factory = $this->getFactory();

		$resolver = function () {
			//
		};

		$factory->get_finder()->shouldReceive( 'add_extension' )->once()->with( 'foo' );
		$factory->get_engine_resolver()->shouldReceive( 'register' )->once()->with( 'bar', $resolver );
		$factory->get_finder()->shouldReceive( 'find' )->once()->with( 'view' )->andReturn( 'path.foo' );
		$factory->get_engine_resolver()->shouldReceive( 'resolve' )->once()->with( 'bar' )->andReturn( $engine = m::mock( Engine::class ) );

		$factory->add_extension( 'foo', 'bar', $resolver );

		$view = $factory->make( 'view', [ 'data' ] );
		$this->assertSame( $engine, $view->get_engine() );
	}

	public function testAddingExtensionPrependsNotAppends() {
		$factory = $this->getFactory();
		$factory->get_finder()->shouldReceive( 'add_extension' )->once()->with( 'foo' );

		$factory->add_extension( 'foo', 'bar' );

		$extensions = $factory->get_extensions();
		$this->assertEquals( 'bar', reset( $extensions ) );
		$this->assertEquals( 'foo', key( $extensions ) );
	}

	public function testPrependedExtensionOverridesExistingExtensions() {
		$factory = $this->getFactory();
		$factory->get_finder()->shouldReceive( 'add_extension' )->once()->with( 'foo' );
		$factory->get_finder()->shouldReceive( 'add_extension' )->once()->with( 'baz' );

		$factory->add_extension( 'foo', 'bar' );
		$factory->add_extension( 'baz', 'bar' );

		$extensions = $factory->get_extensions();
		$this->assertEquals( 'bar', reset( $extensions ) );
		$this->assertEquals( 'baz', key( $extensions ) );
	}

	public function testMakeWithSlashAndDot() {
		$factory = $this->getFactory();
		$factory->get_finder()->shouldReceive( 'find' )->twice()->with( 'foo.bar' )->andReturn( 'path.php' );
		$factory->get_engine_resolver()->shouldReceive( 'resolve' )->twice()->with( 'php' )->andReturn( m::mock( Engine::class ) );
		$factory->make( 'foo/bar' );
		$factory->make( 'foo.bar' );
	}

	public function testNamespacedViewNamesAreNormalizedProperly() {
		$factory = $this->getFactory();
		$factory->get_finder()->shouldReceive( 'find' )->twice()->with( 'vendor/package::foo.bar' )->andReturn( 'path.php' );
		$factory->get_engine_resolver()->shouldReceive( 'resolve' )->twice()->with( 'php' )->andReturn( m::mock( Engine::class ) );
		$factory->make( 'vendor/package::foo/bar' );
		$factory->make( 'vendor/package::foo.bar' );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testExceptionIsThrownForUnknownExtension() {
		$factory = $this->getFactory();
		$factory->get_finder()->shouldReceive( 'find' )->once()->with( 'view' )->andReturn( 'view.foo' );
		$factory->make( 'view' );
	}

	protected function getFactory() {
		return new View_Factory(
			m::mock( Engine_Resolver::class ),
			m::mock( Finder::class )
		);
	}

	protected function getFactoryArgs() {
		return [
			m::mock( Engine_Resolver::class ),
			m::mock( Finder::class )
		];
	}
}
